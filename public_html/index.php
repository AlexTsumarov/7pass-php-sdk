<?php

require __DIR__ . '/../vendor/autoload.php';

$configOptions = require __DIR__ . '/config.local.php';

$action = ltrim(@$_SERVER['PATH_INFO'], '/');

set_exception_handler(function($e) {
  echo __FILE__ . ' Line: ' . __LINE__; var_dump($e); exit; //XXX
});

//SSO::cache()->flush();
$accountId = $configOptions['account_id'];
$config = $configOptions['sso_client'];


//## Usage
$ssoConfig = new P7\SSO\Configuration($config);

$driver = new Stash\Driver\Memcache();
$driver->setOptions(array('servers' => array('127.0.0.1', '11211')));

$ssoConfig->setCachePool(new Stash\Pool($driver));

$sso = new P7\SSO($ssoConfig);



//## Backoffice requests
$tokens = $sso->authorization()->backoffice([
  'account_id' => $accountId
]);

$apiClient = $sso->api($tokens->access_token);
$response = $apiClient->get('/me');

echo __FILE__ . ' Line: ' . __LINE__; var_dump($response->data); exit; //XXX

echo __FILE__ . ' Line: ' . __LINE__; var_dump($response); exit; //XXX

//$va = $sso->authorization()->password([
//  'login' => 'matus@sensible.com',
//  'password' => 'matus@sensible.com'
//]);

return;

$tokens = $sso->authorization()->backoffice([
  'account_id' => $accountId
]);

$apiClient = $sso->api($tokens->access_token);
$apiClient->get('/me');

echo __FILE__ . ' Line: ' . __LINE__; var_dump($va); exit; //XXX

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
