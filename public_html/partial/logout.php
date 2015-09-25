<?php require('partial/header.php')?>

<h2>
  OpenID Connect - Logout
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDLogout" target="_blank" role="button">Documentation</a>
    <a class="btn btn-success" href="<?=$logoutUri?>" role="button">Continue</a>
  </div>
</h2>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">

<pre>
$logoutUri = $sso->authorization()->logoutUri([
  'id_token_hint' => $_SESSION['tokens'],
  'post_logout_redirect_uri' => $logoutRedirectUri
]);
</pre>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $logoutUri
  </div>
  <div class="panel-body">

<pre>
<?php print_r($logoutUri)?>
</pre>

  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $logoutRedirectUri
  </div>
  <div class="panel-body">

<pre>
<?php print_r($config['post_logout_redirect_uri'])?>
</pre>

  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $_SESSION['tokens']
  </div>
  <div class="panel-body">

<pre>
<?php print_r($tokens)?>
</pre>

  </div>
</div>

<?php require('partial/footer.php')?>