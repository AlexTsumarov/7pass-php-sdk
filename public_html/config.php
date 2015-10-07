<?php

$config = [];

if(file_exists('config.local.php')) {
  $config = array_merge($config, require __DIR__ . '/config.local.php');
}

return array_merge($config, $_ENV);