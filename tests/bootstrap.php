<?php
require __DIR__ . '/../lib/P7/SSO.php';

$configure = \VCR\VCR::configure();

//$configure->setStorage('json');

//$configure->enableRequestMatchers(array('method', 'url', 'host', 'query_string', 'body', 'post_fields'));
//$configure->setMode('once');
//$configure->setMode('new_episodes');
$configure->setMode('once');