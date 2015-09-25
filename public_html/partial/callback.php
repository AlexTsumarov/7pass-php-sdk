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

$tokens = $sso->authorization()->callback($payload);

//store tokens into the session
$_SESSION['tokens'] = $tokens;
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