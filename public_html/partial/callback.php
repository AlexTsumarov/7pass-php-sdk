<?php require('partial/header.php')?>

<h2>
  OpenID Connect - Callback
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDAuthorize" target="_blank" role="button">Documentation</a>
    <?php if(!$error):?><a class="btn btn-success" href="/account" role="button">Continue</a><?php endif?>
  </div>
</h2>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">

    <p>
      An user is redirect back to your server now.
      They are either authenticated successfully, cancelled an authentication, or there might be an unexpected server error.
    </p>
    <p>
      In case of an error, error type and description is sent as 'error' and 'error_description' query parameters
      which can be used to render appropriate error message to the user.
    </p>
    <p>
      In order to get an access token, your server should call 7Pass token endpoint -
        this can be achieved using `$sso->authorization()->callback()` method as shown below.
      Returned tokens are specific to this user only and should be stored securely.
      They will be used later for access 7Pass API and 'refresh_token' to obtain renewed 'access_token'.
      In our example, we used PHP session, but SDK doesn't force you to use any particular token set storage.
    </p>
    <p>
      <i><b>Note:</b> '$sso->authorization()->callback()' returns 7P\SSO\TokenSet object which extends from ArrayObject. Even ArrayObject is serializable,
      we still recommend to store plain array into the session instead of 7P\SSO\TokenSet object itself
      as doing so might cause issues during PHP session unserialization.
      </i>
    </p>
<pre>
if(!empty($_GET['error'])) {
  $error = $_GET['error'];
  $errorDescription = $_GET['error_description'];

  //handle and display an error to the user

  return;
}

$code = $_GET['code'];

$payload = [
  'redirect_uri' => $callbackUri,
  'code' => $code
];

$tokenSet = $sso->authorization()->callback($payload);

//store tokens into user's session storage
$_SESSION['tokens'] = $tokenSet->getArrayCopy();
</pre>

  </div>
</div>

<?php if($error):?>

  <div class="panel panel-danger">
    <div class="panel-heading">
      Error
    </div>
    <div class="panel-body">

<pre>
Error: <?php print_r($error)?>
<br>
Description: <?php print_r($errorDescription)?>
</pre>

    </div>
  </div>

  <?php
  return;
endif
?>

<div class="panel panel-default">
  <div class="panel-heading">
    $tokens
  </div>
  <div class="panel-body">

<pre>
<?php print_r($tokens)?>
</pre>

  </div>
</div>

<?php require('partial/footer.php')?>