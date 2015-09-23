<?php

require __DIR__ . '/../vendor/autoload.php';
use \P7\SSO;

$config = require __DIR__ . '/config.local.php';

$action = ltrim(@$_SERVER['PATH_INFO'], '/');

set_exception_handler(function($e) {
  echo __FILE__ . ' Line: ' . __LINE__; var_dump($e); exit; //XXX
});

//SSO::cache()->flush();

$ssoConfig = new SSO\Configuration($config['sso_client']);
$ssoConfig->getCachePool()->flush();

$sso = new SSO($ssoConfig);

$sso->authorization()->password([
  'login' => 'matus@sensible.com',
  'password' => 'matus@sensible.com'
]);

return;

$callback_uri = 'http://' . $_SERVER['HTTP_HOST'] . '/callback';


// Redirect to login url
if($action == 'login') {
  $uri = $sso->authorization()->uri([
    'redirect_uri' => $callback_uri
  ]);

  header('Location: ' . $uri);
}
?>

<ul>
  <li><a href='/login'>Login</a></li>
  <li><a href='/backoffice'>Backoffice</a></li>
</ul>

<pre>
<?php
if($action == 'backoffice') {

  try {
    $tokens = $sso->authorization()->backoffice($config['account_id']);
    echo '<h3>Success</h3>';
    print_r($tokens);

  } catch(Exception $e) {
    echo '<h3>Error</h3>';
    print_r($e);
  }
}
?>
<?php

if($action == 'callback') {
  $code = $_GET['code'];

  $payload = [
    'redirect_uri' => $callback_uri,
    'code' => $code
  ];

  try {
    $tokens = $sso->authorization()->callback($payload);

    echo '<h3>#callback(' . var_export($payload, true) . ')</h3>';
    print_r($tokens);

    $payload = ['refresh_token' => $tokens->refresh_token];
    echo '<h3>#refresh(' . var_export($payload, true) . ')</h3>';
    $tokens = $sso->authorization()->refresh($payload);
    print_r($tokens);

    $api = $sso->api($tokens->access_token);

    echo '<h3>GET /me</h3>';
    print_r($api->get('/me')->data);

    echo '<h3>GET /me/emails</h3>';
    print_r($api->get('/me/emails')->data);
  } catch(Exception $e) {
    echo '<h3>Error</h3>';
    print_r($e->getMessage());
  }
}
