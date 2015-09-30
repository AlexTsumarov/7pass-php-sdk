<?php require('partial/header.php')?>

<h2>
  OpenID Connect - Logout
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDLogout" target="_blank" role="button">Documentation</a>
    <a class="btn btn-success" href="<?=$logoutUri?>" role="button">Continue</a>
  </div>
</h2>

<p>
  To sign out the user, you need to remove the user specific data from
  the session and, depending on your application, from other
  storages. Once the session (and others) are cleared, you can futher
  decide whether you not only want to sign out the user from your
  application but also from the 7Pass SSO service itself. To do that,
  you need to provide the "about to be removed" tokens and specify a
  post logout URL. The user will be redirected to 7Pass SSO and
  immediately back to the specified post logout URL. Note that this
  post logout URL needs to be registered for the client.
</p>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Signing out the user</h3>
  </div>
  <div class="panel-body">
    <p>
      The library will again handle the generation of the URL on its
      own, you just need to pass two arguments: <b>id_token_hint</b>
      and <b>post_logout_redirect_uri</b>. The value of
      the <b>id_token_hint</b> can be either the ID token directly or
      the <i>TokenSet</i> object (the library will extract the ID
      token automatically). The <b>post_logout_redirect_uri</b> needs
      to be registered to the client and the user will be redirected
      to it afterwards.
    </p>

<pre class="prettyprint">
// Deserialize the tokens from the session storage
$tokens = new \P7\SSO\TokenSet($_SESSION['tokens']);

// Remove the authentication data from the session
unset($_SESSION['tokens']);

// Generate the logout URL
$logoutUri = $sso->authorization()->logoutUri([
  'id_token_hint' => $tokens,
  'post_logout_redirect_uri' => $logoutRedirectUri
]);

// Redirect the user
header('Location: ' . $logoutUri);
exit;
</pre>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $logoutRedirectUri
  </div>
  <div class="panel-body">

<pre class="prettyprint">
<?php print_r($config['post_logout_redirect_uri'])?>
</pre>

  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $logoutUri
  </div>
  <div class="panel-body">

<pre class="prettyprint">
<?php print_r($logoutUri)?>
</pre>

  </div>
</div>

<p>
  <b>To continue the demonstration, please use the Continue button above</b>.
</p>

<?php require('partial/footer.php')?>
