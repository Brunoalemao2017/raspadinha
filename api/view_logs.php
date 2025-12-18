<?php
$logFile = __DIR__ . '/payment_error.log';
if (file_exists($logFile)) {
    echo "<pre>";
    echo htmlspecialchars(file_get_contents($logFile));
    echo "</pre>";
} else {
    echo "No log file found.";
}
?>