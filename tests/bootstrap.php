<?php
require __DIR__ . '/../lib/P7/SSO.php';

$configure = \VCR\VCR::configure();

$configure->enableRequestMatchers(array('method', 'url', 'host', 'query_string', 'body', 'post_fields'));
//$configure->setMode('new_episodes');
$configure->setMode('none');