<?php
/**
 * Script para limpar cache e forçar atualização do logo
 * Execute este arquivo após alterar o logo
 */

session_start();
include '../conexao.php';

// Verificar se é admin
$usuarioId = $_SESSION['usuario_id'] ?? null;
if (!$usuarioId) {
    die('Você precisa estar logado!');
}

$admin = ($stmt = $pdo->prepare("SELECT admin FROM usuarios WHERE id = ?"))->execute([$usuarioId]) ? $stmt->fetchColumn() : null;
if ($admin != 1) {
    die('Você não é um administrador!');
}

echo "<!DOCTYPE html>";
echo "<html lang='pt-BR'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>Limpar Cache do Logo</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; background: #000; color: #fff; padding: 20px; }";
echo ".container { max-width: 800px; margin: 0 auto; background: #1a1a1a; padding: 30px; border-radius: 10px; }";
echo ".success { color: #22c55e; padding: 10px; background: rgba(34, 197, 94, 0.1); border-radius: 5px; margin: 10px 0; }";
echo ".error { color: #ef4444; padding: 10px; background: rgba(239, 68, 68, 0.1); border-radius: 5px; margin: 10px 0; }";
echo ".info { color: #3b82f6; padding: 10px; background: rgba(59, 130, 246, 0.1); border-radius: 5px; margin: 10px 0; }";
echo "h1 { color: #fed000; }";
echo "a { color: #fed000; text-decoration: none; }";
echo "a:hover { text-decoration: underline; }";
echo ".btn { background: #fed000; color: #000; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 10px 5px; }";
echo ".btn:hover { background: #e5bd00; }";
echo "</style>";
echo "</head>";
echo "<body>";
echo "<div class='container'>";
echo "<h1>🔄 Limpeza de Cache do Logo</h1>";

// 1. Verificar configuração atual do logo
$config = $pdo->query("SELECT logo FROM config LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$logoPath = $config['logo'] ?? null;

echo "<h2>📋 Informações Atuais</h2>";
echo "<div class='info'>";
echo "<strong>Caminho do logo no banco:</strong> " . htmlspecialchars($logoPath ?? 'Nenhum logo configurado') . "<br>";

if ($logoPath) {
    $fullPath = $_SERVER['DOCUMENT_ROOT'] . $logoPath;
    echo "<strong>Caminho completo:</strong> " . htmlspecialchars($fullPath) . "<br>";

    if (file_exists($fullPath)) {
        echo "<strong>Status do arquivo:</strong> ✅ Arquivo existe<br>";
        echo "<strong>Tamanho:</strong> " . round(filesize($fullPath) / 1024, 2) . " KB<br>";
        echo "<strong>Última modificação:</strong> " . date('d/m/Y H:i:s', filemtime($fullPath)) . "<br>";

        // Mostrar o logo atual
        echo "<br><strong>Logo atual:</strong><br>";
        echo "<img src='" . htmlspecialchars($logoPath) . "?v=" . time() . "' alt='Logo' style='max-width: 300px; background: #fff; padding: 10px; border-radius: 5px; margin-top: 10px;'>";
    } else {
        echo "<strong>Status do arquivo:</strong> ❌ Arquivo NÃO existe no servidor<br>";
        echo "<div class='error'>O arquivo do logo não foi encontrado! Você precisa fazer upload novamente.</div>";
    }
}
echo "</div>";

// 2. Limpar OPcache se estiver habilitado
echo "<h2>🧹 Limpeza de Cache</h2>";

if (function_exists('opcache_reset')) {
    if (opcache_reset()) {
        echo "<div class='success'>✅ OPcache limpo com sucesso!</div>";
    } else {
        echo "<div class='error'>❌ Falha ao limpar OPcache</div>";
    }
} else {
    echo "<div class='info'>ℹ️ OPcache não está habilitado</div>";
}

// 3. Limpar cache de sessão
if (session_status() === PHP_SESSION_ACTIVE) {
    session_regenerate_id(true);
    echo "<div class='success'>✅ ID de sessão regenerado</div>";
}

// 4. Verificar permissões de diretório
$uploadDir = $_SERVER['DOCUMENT_ROOT'] . '/assets/upload/';
if (file_exists($uploadDir)) {
    if (is_writable($uploadDir)) {
        echo "<div class='success'>✅ Diretório de upload tem permissões de escrita</div>";
    } else {
        echo "<div class='error'>❌ Diretório de upload NÃO tem permissões de escrita</div>";
    }
} else {
    echo "<div class='error'>❌ Diretório de upload não existe</div>";
}

// 5. Listar todos os arquivos no diretório de upload
echo "<h2>📁 Arquivos no Diretório de Upload</h2>";
if (file_exists($uploadDir)) {
    $files = scandir($uploadDir);
    $imageFiles = array_filter($files, function ($file) use ($uploadDir) {
        return is_file($uploadDir . $file) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $file);
    });

    if (count($imageFiles) > 0) {
        echo "<div class='info'>";
        echo "<strong>Imagens encontradas:</strong><br>";
        foreach ($imageFiles as $file) {
            $filePath = $uploadDir . $file;
            $fileSize = round(filesize($filePath) / 1024, 2);
            $fileDate = date('d/m/Y H:i:s', filemtime($filePath));
            echo "• " . htmlspecialchars($file) . " (" . $fileSize . " KB) - " . $fileDate . "<br>";
        }
        echo "</div>";
    } else {
        echo "<div class='info'>Nenhuma imagem encontrada no diretório de upload</div>";
    }
}

echo "<h2>🔧 Ações Disponíveis</h2>";
echo "<div style='margin: 20px 0;'>";
echo "<a href='config.php' class='btn'>← Voltar para Configurações</a>";
echo "<button onclick='location.reload()' class='btn'>🔄 Recarregar Página</button>";
echo "<button onclick='clearBrowserCache()' class='btn'>🗑️ Limpar Cache do Navegador</button>";
echo "</div>";

echo "<h2>💡 Dicas para Resolver o Problema</h2>";
echo "<div class='info'>";
echo "<ol>";
echo "<li><strong>Limpe o cache do navegador:</strong> Pressione Ctrl+Shift+Delete (Windows) ou Cmd+Shift+Delete (Mac)</li>";
echo "<li><strong>Tente em modo anônimo:</strong> Abra uma janela anônima/privada do navegador</li>";
echo "<li><strong>Force o reload:</strong> Pressione Ctrl+F5 (Windows) ou Cmd+Shift+R (Mac)</li>";
echo "<li><strong>Verifique o arquivo:</strong> Certifique-se de que o arquivo foi realmente enviado</li>";
echo "<li><strong>Tente outro navegador:</strong> Teste em um navegador diferente</li>";
echo "</ol>";
echo "</div>";

echo "<script>";
echo "function clearBrowserCache() {";
echo "  if (confirm('Isso irá recarregar a página. Continuar?')) {";
echo "    // Força reload sem cache";
echo "    window.location.href = window.location.href + '?nocache=' + new Date().getTime();";
echo "  }";
echo "}";
echo "</script>";

echo "</div>";
echo "</body>";
echo "</html>";
?>