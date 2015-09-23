<?php
require __DIR__ . '/../vendor/autoload.php';

$configOptions = require __DIR__ . '/config.php';

$action = ltrim(@$_SERVER['PATH_INFO'], '/');

echo __FILE__ . ' Line: ' . __LINE__; var_dump($action);
echo __FILE__ . ' Line: ' . __LINE__; var_dump($_SERVER); exit; //XXX

set_exception_handler(function($e) {
  echo '<h3>Error</h3>';
  print_r($e);
});

$accountId = $configOptions['account_id'];

//creates configuration
$ssoConfig = new P7\SSO\Configuration($configOptions);

//set custom cache driver
$cacheDriver = new Stash\Driver\FileSystem();
$ssoConfig->setCachePool(new Stash\Pool($cacheDriver));

//creates SSO object
$sso = new P7\SSO($ssoConfig);

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
if($action == 'backoffice'):

  $tokens = $sso->authorization()->backoffice([
    'account_id' => $accountId
  ]);
  echo '<h3>Success</h3>';
  print_r($tokens);

endif;

if($action == 'callback'):

  $code = $_GET['code'];

  $payload = [
    'redirect_uri' => $callback_uri,
    'code' => $code
  ];

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

endif;
