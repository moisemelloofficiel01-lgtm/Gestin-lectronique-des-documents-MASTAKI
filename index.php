<?php
$basePath = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$basePath = rtrim($basePath, '/');
$target = ($basePath === '' ? '' : $basePath) . '/src/';

header('Location: ' . $target);
exit;
