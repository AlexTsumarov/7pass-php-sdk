<?php require('partial/header.php')?>

<h2>Account details</h2>

<p>
At this point in the process, we have the user's tokens and can start
interacting with the 7Pass SSO service to get further details about
the user.
</p>

<p>
  <b>To see the Logout process, please use the Logout button above.</b>
</p>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Making sure the access token is fresh</h3>
  </div>
  <div class="panel-body">
    <p>
      First, we need to retrieve the serialized tokens from the
      storage. In this example, we're just using the session. Second,
      we need to check whether the access token has expired. If it
      has, we need to ask for a new one. Every time you generate new
      tokens, you should save them back into the storage.
    </p>

<pre class="prettyprint">
$tokensArray = $_SESSION['tokens'];
$tokens = new \P7\SSO\TokenSet($tokensArray);

// Check whether the access token has expired and refresh it if it
// has.
if($tokens->isAccessTokenExpired()) {
  $tokens = $sso->authorization()->refresh($tokens);

  // Store the refreshed tokens back into user's session storage.
  $_SESSION['tokens'] = $tokens->getArrayCopy();
}
</pre>
  </div>
</div>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Making requests</h3>
  </div>
  <div class="panel-body">
    <p>
      Now that we're sure the tokens are up to date, we can start
      making requests to the 7Pass SSO service to get the user
      data. Notice the "Documentation" buttons next to the example
      outputs which lead to the official documentation of the
      particular endpoint.
    </p>
    <p>
      The 7Pass SSO service offers quite a few of these endpoints. To
      learn more about them, you can go to
      the <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Accounts"
      target="_blank">oficial documentation's overview</a>.
    </p>

<pre class="prettyprint">
// Create a client object using the tokens
$accountClient = $sso->accountClient($tokens);

$me = $accountClient->get('me');
$emails = $accountClient->get('me/emails');
</pre>
  </div>
</div>

<div>
  <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Accounts-GetAccount" class="btn btn-info pull-right" target="_blank">Documentation</a>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    $me = $accountClient->get('me')
  </div>
  <div class="panel-body">
    <pre class="prettyprint">
      <?php var_dump($me)?>
    </pre>
  </div>
</div>

<div>
  <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Emails-GetAccountEmails" class="btn btn-info pull-right" target="_blank">Documentation</a>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    $emails = $accountClient->get('me/emails')
  </div>
  <div class="panel-body">
  <pre class="prettyprint">
    <?php var_dump($emails)?>
  </pre>
  </div>
</div>


<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Batch requests</h3>
  </div>
  <div class="panel-body">
    <p>
      Often you will have the need to make more than one request to
      get the data you need. In order to make the process easier both
      on the servers and the developer, you can use the batch
      functionality to specify the endpoints you want to fetch data
      from and perform a single request afterwards.
    </p>
    <p>
      The names of the keys are up to you do decide (they will be
      honored in the response). The values of course need to
      correspond to an existing endpoint.
    </p>

<pre class="prettyprint">
$batch = $accountClient->batch([
  'getUserInfo' => 'me',
  'getConsents' => 'me/consents'
]);
</pre>
  </div>
</div>

<div>
  <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Accounts-Batch" class="btn btn-info pull-right" target="_blank">Documentation</a>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    $accountClient->batch([
      'getUserInfo' => 'me',
      'getConsents' => 'me/consents'
    ]);
  </div>
  <div class="panel-body">
<pre class="prettyprint">
  <?php var_dump($batch)?>
</pre>
  </div>
</div>


<div>
  <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDUserInfo" class="btn btn-info pull-right" target="_blank">Documentation</a>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    $userInfo = $accountClient->get('/connect/v1.0/userInfo');
  </div>
  <div class="panel-body">
<pre class="prettyprint">
  <?php var_dump($userInfo)?>
</pre>
  </div>
</div>

<p>
  The 7Pass SSO service offers more endpoints you can interact
  with. To learn more about them, you can go to
  the <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Accounts"
  target="_blank">oficial documentation's overview</a>. If you have
  any questions or something's not working as expected, please do not
  hesitate to contact Filip Skokan
  at <a href="mailto:filip.skokan@prosiebensat1.com?subject=7Pass Dev
  Account" target="_blank">filip.skokan@prosiebensat1.com</a>.
</p>

<?php require('partial/footer.php')?>
