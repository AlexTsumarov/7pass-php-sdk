<?php
require __DIR__ . '/../lib/P7/SSO.php';

$config = \VCR\VCR::configure();
$config->setMode('none');
$config->enableRequestMatchers(array('method', 'url', 'host', 'query_string', 'body', 'post_fields'));