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

//creates configuration
$ssoConfig = new P7\SSO\Configuration($config);

//creates SSO object
$sso = new P7\SSO($ssoConfig);

$callbackUri = $config['redirect_uri'];

function getSessionTokens($throws = false) {
  if($throws && empty($_SESSION['tokens'])) {
    throw new \Exception("No session tokens");
  }

  return empty($_SESSION['tokens']) ? null : new \P7\SSO\TokenSet($_SESSION['tokens']);
}

function authenticatedRedirect() {
  $tokens = getSessionTokens();

  if(!$tokens) {
    return false;
  }

  header('Location: /account');
  exit;
}

$loggedIn = getSessionTokens() ? true : false;

switch($action) {

  case 'login':

    authenticatedRedirect();

    $uri = $sso->authorization()->authorizeUri([
      'redirect_uri' => $callbackUri
    ]);

    require('partial/login.php');

    break;


  case 'login-redirect':

    authenticatedRedirect();

    $uri = $sso->authorization()->authorizeUri([
      'redirect_uri' => $callbackUri
    ]);

    header('Location: ' . $uri);

    break;


  case 'callback':

    authenticatedRedirect();

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

    $loggedIn = false;
    unset($_SESSION['tokens']);

    $logoutUri = $sso->authorization()->logoutUri([
      'id_token_hint' => $tokens->id_token,
      'post_logout_redirect_uri' => $config['post_logout_redirect_uri']
    ]);

    require('partial/logout.php');

    break;

  case 'logout-callback':

    require('partial/logout-callback.php');

    break;


  case 'account':

    $tokens = getSessionTokens(true);

    if($tokens->isAccessTokenExpired()) {
      $tokens = $sso->authorization()->refresh($tokens);
      $_SESSION['tokens'] = $tokens->getArrayCopy();
    }

    $accountClient = $sso->accountClient($tokens);

    $me = $accountClient->get('/me');

    $emails = $accountClient->get('/me/emails');

    $batch = $accountClient->batch([
      'getUserInfo' => '/me',
      'getConsents' => '/me/consents'
    ]);

    require('partial/account.php');

    break;


  default:
    $action = 'index';
    require('partial/index.php');
    break;
}
