<?php require('partial/header.php')?>

<h2>
  OpenID Connect - Authorize
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDAuthorize" target="_blank" role="button">Documentation</a>
    <a class="btn btn-success" href="<?=$uri?>" role="button">Continue</a>
  </div>
</h2>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">

    <p>
      In order to authenticate an user on your site using 7Pass SSO provider, you need to get an URL of 7Pass authorize endpoint specific to your application.
      Using an '$sso->authorization()' helper this URL is automatically generated for you using configuration values provided (e.g. 'client_id' or an 'environment').
    </p>

<pre>
$uri = $sso->authorization()->authorizeUri([
  'redirect_uri' => $callbackUri
]);

//redirect an user to $uri
</pre>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $callbackUri
  </div>
  <div class="panel-body">

<pre>
<?php print_r($callbackUri)?>
</pre>

  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $uri
  </div>
  <div class="panel-body">

<pre>
<?php print_r($uri)?>
</pre>

  </div>
</div>

<?php require('partial/footer.php')?>