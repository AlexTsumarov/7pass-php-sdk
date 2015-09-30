<?php require('partial/header.php')?>

<h2>
  OpenID Connect - Callback
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDAuthorize" target="_blank" role="button">Documentation</a>
    <?php if(empty($error)):?><a class="btn btn-success" href="/account" role="button">Continue</a><?php endif?>
  </div>
</h2>

<p>
  After the user has finished with the sign in/up dialog, he/she
  has been redirected to the <b>redirect_uri</b> URL with the
  outcome of the sign in process. The user might have successfully
  authenticated but also might have decided to cancel the process
  or some other error might have happened. Therefore it's
  important have proper error handling.
</p>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Error handling</h3>
  </div>
  <div class="panel-body">
    <p>
      Whenever an error occurs, there will be two query parameters
      present in the URL - <b>error</b>
      and <b>error_description</b>. The <b>error</b> parameter
      contains an error code and the <b>error_description</b> contains
      a human readable description of the error suitable for direct
      displaying to the user.
    </p>
<pre class="prettyprint">
if(!empty($_GET['error'])) {
  $error = $_GET['error'];
  $errorDescription = $_GET['error_description'];

  // Display the errors to the user to let him know the reason the
  // process has failed here.
}
</pre>
  </div>
</div>

<?php if(!empty($error)):?>

  <div class="panel panel-danger">
    <div class="panel-heading">
      Error
    </div>
    <div class="panel-body">

<pre class="prettyprint">
Error: <?php print_r($error)?>

Description: <?php print_r($errorDescription)?>
</pre>

    </div>
  </div>

  <?php
  return;
endif
?>

</p>
    In case there's been no error, there will be a <b>code</b>
    parameter in the URL. This code along with the <b>redirect_uri</b>
    can be used to retrieve the tokens which will later allow you to
    fetch the actual information about the user. These tokens are
    specific to the particular user and are private. You need to keep
    them secured and do not share them with anybody.
<p>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Retrieving the tokens</h3>
  </div>
  <div class="panel-body">
    <p>
      The library will handle retrieving the tokens on its own, you
      just need to provide the <b>redirect_uri</b> and also
      the <b>code</b> parameter from the current URL. Note that
      <b>the code can be used only once</b> - an exception is thrown
      otherwise. An exception can be thrown for other reasons as well
      (invalid redirect URL etc.).
    </p>
<pre class="prettyprint">
$code = $_GET['code'];

$payload = [
  'redirect_uri' => $callbackUri,
  'code' => $code
];

$tokenSet = $sso->authorization()->callback($payload); // Beware: May throw an exception!
$tokens = $tokenSet->getArrayCopy();

// Store tokens into the user's session storage. You might also want
// to store them in your persistent storage (i.e. your database) or
// any other storage of your own choosing.
$_SESSION['tokens'] = $tokens
</pre>
    <p>
      <b>Note:</b> <i>$sso->authorization()->callback()</i>
      returns <i>P7\SSO\TokenSet</i> object which extends from
      <i>ArrayObject</i>. Even though <i>ArrayObject</i> is
      serializable, we still recommend to store plain array into the
      session instead of the <i>TokenSet</i> object itself to avoid
      any possible issues with the serialization process using
      the <i>getArrayCopy</i> method.
    </p>
  </div>
</div>

<p>
  When you inspect the <i>$tokens</i>, you can see a few
  things. First, there's an access token. The access token is as an
  authentication mechanism and is send along side the user specific
  requests. Its validity is however limited to just 2 hours (7200
  seconds) from the moment it was generated and received (notice the
  received_at field). If the token has expired, it can no longer be
  used and it's necessary to ask for another one using the refresh
  token. The expiration time of the refresh token itself is set to 60
  days. You'll see how to ask for the new access token in the next
  step. Finally, there's an ID token which contains some basic
  information about the user and it's automatically decoded for
  convenience.
</p>
<div class="panel panel-default">
  <div class="panel-heading">
    $tokens
  </div>
  <div class="panel-body">

<pre class="prettyprint">
<?php print_r($tokens->getArrayCopy())?>
</pre>

  </div>
</div>

<p>
  <b>To continue the demonstration, please use the Continue button above</b>.
</p>

<?php require('partial/footer.php')?>
