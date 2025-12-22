# 🔧 Guia de Solução - Logo Não Atualiza

## Problema
O logo não está mudando mesmo após fazer upload de um novo arquivo.

## Soluções Implementadas

### 1. Script de Diagnóstico
Criamos um script que você pode acessar para diagnosticar o problema:

**URL:** `http://seu-site.com/admin/limpar_cache_logo.php`

Este script irá:
- ✅ Mostrar informações sobre o logo atual
- ✅ Verificar se o arquivo existe no servidor
- ✅ Limpar o cache do PHP (OPcache)
- ✅ Listar todos os arquivos de imagem no diretório
- ✅ Fornecer dicas para resolver o problema

### 2. Melhorias no Upload
Atualizamos o código de upload para:
- ✅ Adicionar timestamp no nome do arquivo (força atualização)
- ✅ Limpar automaticamente o OPcache após upload
- ✅ Regenerar ID de sessão
- ✅ Deletar logo antigo automaticamente

### 3. Cache-Busting Automático
Adicionamos `?v=<?= time() ?>` em todos os lugares onde o logo é exibido:
- ✅ Header do site
- ✅ Footer do site
- ✅ Página de configurações do admin
- ✅ Sidebar mobile

## Como Resolver o Problema Agora

### Método 1: Usar o Script de Diagnóstico
1. Acesse: `http://localhost/admin/limpar_cache_logo.php` (ou seu domínio)
2. Veja as informações sobre o logo atual
3. Clique em "Limpar Cache do Navegador"
4. Volte para a página de configurações

### Método 2: Fazer Upload Novamente
1. Vá em **Admin → Configurações**
2. Faça upload de um novo logo
3. Clique em "Salvar Configurações"
4. O sistema agora irá:
   - Deletar o logo antigo
   - Salvar o novo com timestamp único
   - Limpar o cache automaticamente

### Método 3: Limpar Cache Manualmente

#### No Navegador:
- **Chrome/Edge:** Pressione `Ctrl + Shift + Delete` → Limpar cache
- **Firefox:** Pressione `Ctrl + Shift + Delete` → Limpar cache
- **Safari:** Pressione `Cmd + Option + E`

#### Forçar Reload:
- **Windows:** `Ctrl + F5` ou `Ctrl + Shift + R`
- **Mac:** `Cmd + Shift + R`

#### Modo Anônimo:
- Abra uma janela anônima/privada e teste

### Método 4: Verificar Servidor
Se estiver usando XAMPP/WAMP:
1. Reinicie o Apache
2. Verifique se o diretório `/assets/upload/` tem permissões de escrita
3. Verifique se o arquivo foi realmente enviado

## Checklist de Verificação

- [ ] O arquivo do logo existe em `/assets/upload/`?
- [ ] O caminho no banco de dados está correto?
- [ ] Você limpou o cache do navegador?
- [ ] Você tentou em modo anônimo?
- [ ] Você tentou em outro navegador?
- [ ] O servidor Apache foi reiniciado?

## Comandos Úteis (se necessário)

### Verificar permissões (Linux/Mac):
```bash
chmod 755 assets/upload/
```

### Limpar cache do PHP (se tiver acesso SSH):
```bash
php -r "opcache_reset();"
```

## Arquivos Modificados

1. `/admin/config.php` - Melhorado upload e cache-busting
2. `/admin/limpar_cache_logo.php` - Novo script de diagnóstico
3. `/inc/header.php` - Já tinha cache-busting implementado

## Próximos Passos

1. **Acesse o script de diagnóstico** para ver o status atual
2. **Faça upload de um novo logo** usando a página de configurações
3. **Limpe o cache do navegador** usando Ctrl+Shift+Delete
4. **Teste em modo anônimo** para confirmar

## Suporte

Se o problema persistir após seguir todos os passos:
1. Verifique os logs de erro do PHP
2. Verifique se há erros no console do navegador (F12)
3. Confirme que o arquivo foi realmente enviado para o servidor
4. Verifique as permissões do diretório `/assets/upload/`

---

**Última atualização:** 22/12/2025
