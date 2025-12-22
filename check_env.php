<?php
header('Content-Type: application/json');
echo json_encode([
    'php_version' => PHP_VERSION,
    'curl_enabled' => function_exists('curl_init'),
    'pdo_enabled' => extension_loaded('pdo'),
    'pdo_mysql_enabled' => extension_loaded('pdo_mysql'),
    'display_errors' => ini_get('display_errors'),
    'extensions' => get_loaded_extensions()
]);
?>