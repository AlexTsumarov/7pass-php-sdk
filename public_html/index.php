<?php

require __DIR__ . '/../vendor/autoload.php';
use \P7\SSO;

$action = ltrim(@$_SERVER['PATH_INFO'], '/');

SSO::cache()->flush();

$sso = new SSO(
  [
    'client_id' => '55b0b8964a616e16b9320000',
    'client_secret' => '6b776407825a50b0f72941315194a3d50886b86b81bc40bbcf1714bdf50b3aa4',
    'environment' => 'development'
  ]
);

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
</ul>

<pre>
<?php

if($action == 'callback') {
  $code = $_GET['code'];

  $payload = [
    'redirect_uri' => $callback_uri,
    'code' => $code
  ];

  $response = $sso->authorization()->callback($payload);

  if($response->success) {
    $tokens = $response->data;
    echo '<h3>#callback(' . var_export($payload, true) . ')</h3>';
    print_r($tokens);

    $payload = ['refresh_token' => $tokens->refresh_token];
    echo '<h3>#refresh(' . var_export($payload, true) . ')</h3>';
    $tokens = $sso->authorization()->refresh($payload)->data;
    print_r($tokens);

    $api = $sso->api($tokens->access_token);

    echo '<h3>GET /me</h3>';
    print_r($api->get('/me')->data);

    echo '<h3>GET /me/emails</h3>';
    print_r($api->get('/me/emails')->data);
  } else {
    echo 'Invalid or already used token.';
  }
}
