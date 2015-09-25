<?php
require __DIR__ . '/../vendor/autoload.php';

session_start();

$config = require __DIR__ . '/config.php';

$url = parse_url($_SERVER['REQUEST_URI']);
$action = ltrim($url['path'], '/');

set_exception_handler(function($e) {
  $exception = $e;
  require('partial/exception.php');
});

$accountId = $config['account_id'];

//creates configuration
$ssoConfig = new P7\SSO\Configuration($config);

//set custom cache driver
$cacheDriver = new Stash\Driver\FileSystem();
$ssoConfig->setCachePool(new Stash\Pool($cacheDriver));

//creates SSO object
$sso = new P7\SSO($ssoConfig);

$callbackUri = $config['redirect_uri'];

function getSessionTokens($throws = false) {
  if($throws && empty($_SESSION['tokens'])) {
    throw new \Exception("No session tokens");
  }

  return $_SESSION['tokens'];
}

$loggedIn = getSessionTokens() ? true : false;

switch($action) {

  case 'login':
    $uri = $sso->authorization()->authorizeUri([
      'redirect_uri' => $callbackUri
    ]);

    require('partial/login.php');

    break;


  case 'login-redirect':
    $uri = $sso->authorization()->authorizeUri([
      'redirect_uri' => $callbackUri
    ]);

    header('Location: ' . $uri);

    break;


  case 'callback':

    if(!empty($_GET['error'])) {
      $error = $_GET['error'];
      $errorDescription = $_GET['error_description'];

      require('partial/callback.php');
      break;
    }

    $code = $_GET['code'];

    $payload = [
      'redirect_uri' => $callbackUri,
      'code' => $code
    ];

    $tokens = $sso->authorization()->callback($payload);

    $_SESSION['tokens'] = $tokens;

    require('partial/callback.php');

    break;


  case 'logout':

    $tokens = getSessionTokens(true);

    $logoutUri = $sso->authorization()->logoutUri([
      'id_token_hint' => $tokens->id_token,
      'post_logout_redirect_uri' => $config['post_logout_redirect_uri']
    ]);

    require('partial/logout.php');

    break;

  case 'logout-callback':

    $loggedIn = false;
    unset($_SESSION['tokens']);

    require('partial/logout-callback.php');

    break;


  case 'account':

    $tokens = getSessionTokens(true);

//    $payload = ['refresh_token' => $tokens->refresh_token];
//    echo '<h3>#refresh(' . var_export($payload, true) . ')</h3>';
//    $tokens = $sso->authorization()->refresh($payload);
//    print_r($tokens);

    $accountClient = $sso->accountClient($tokens->access_token);

    $me = $accountClient->get('/me')->data;

    $emails = $accountClient->get('/me/emails')->data;

    require('partial/account.php');

    break;


  default:
    require('partial/index.php');
    break;
}
