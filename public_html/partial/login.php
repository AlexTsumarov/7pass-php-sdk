<?php require('partial/header.php')?>

<h2>
  Initialization of OpenID Connect Authorize flow
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDAuthorize" target="_blank" role="button">7Pass documentation</a>
    <a class="btn btn-success" href="<?=$uri?>" role="button">Continue</a>
  </div>
</h2>

<p>
  Now that the library is set up, we can initiate the authorize
  flow. The core principle is that the user is redirected to 7Pass SSO
  using a specially crafted URL. Once the user reaches the
  destination, he/she is presented with a sign in/up dialog. If the
  user has signed in before, the dialog is skipped. In both scenarios,
  the user is ultimately redirected to a URL called
  <b>redirect_uri</b>. This URL is associated with the client to make
  sure only verified hosts can make the request. A client is allowed
  to have multiple redirect URLs associated with it and therefore we
  need to provide the correct URL before the redirect takes place.
</p>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">
    <p>
      The library automatically handles the generation of the URL to
      which the user needs to be redirected. The only required
      parameter is the <b>redirect_uri</b> URL. The URL can be
      arbitrary (given that it is registered to the client) but will
      by convention lead to the same host and a route called
      "callback". Using the objects from the previous page, we can
      proceed as follows:
    </p>

<pre class="prettyprint">
// Generate the URL
$uri = $sso->authorization()->authorizeUri([
  'redirect_uri' => $callbackUri
]);

// Redirect the user to the generated URL.
header('Location: ' . $uri);
exit;
</pre>
  </div>
</div>

<p>
If we look closely at the generated URL, we can see that it redirects
to the configured 7Pass SSO with a few parameters. Most importantly,
it contains the passed <b>redirect_uri</b>.
</p>

<div class="panel panel-default">
    <div class="panel-heading">
    $callbackUri
    </div>
<div class="panel-body">
<pre class="prettyprint">
<?php print_r($callbackUri)?>
</pre>
</div>
</div>

<div class="panel panel-default">
  <div class="panel-heading">
    $uri
  </div>
  <div class="panel-body">

<pre class="prettyprint">
<?php print_r($uri)?>
</pre>

  </div>
</div>

<p>
  <b>To continue the demonstration, please use the Continue button above</b>.
</p>

<?php require('partial/footer.php')?>