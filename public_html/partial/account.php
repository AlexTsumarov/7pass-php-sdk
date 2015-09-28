<?php require('partial/header.php')?>

<h2>
  Account details
</h2>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">
    <p>
      Your user should be successfully logged in now and user's tokens stored.
      This section shows how obtained tokens can be further used to call 7Pass API endpoints.
      It also shows how to handle access token refresh using this SDK.
    </p>
    <p>

    </p>

<pre>
$tokensArray = $_SESSION['tokens'];
$tokens = new \7P\SSO\TokenSet($tokensArray);

//refresh access token if it's due to expire
if($tokens->isAccessTokenExpired()) {
  $tokens = $sso->authorization()->refresh($tokens);

  //store refreshed token set into user's session storage
  $_SESSION['tokens'] = $tokens->getArrayCopy();
}

//create an account API client
$accountClient = $sso->accountClient($tokens);

$me = $accountClient->get('/me')->data;
$emails = $accountClient->get('/me/emails')->data;
</pre>
  </div>
</div>

<div>
  <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Accounts-GetAccount" class="btn btn-default pull-right" target="_blank">Documentation</a>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    $accountClient->get('/me')
  </div>
  <div class="panel-body">
    <pre>
      <?php print_r($me)?>
    </pre>
  </div>
</div>

<div>
  <a href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-Emails-GetAccountEmails" class="btn btn-default pull-right" target="_blank">Documentation</a>
</div>
<div class="panel panel-default">
  <div class="panel-heading">
    $accountClient->get('/me/emails')
  </div>
  <div class="panel-body">
  <pre>
    <?php print_r($emails)?>
  </pre>
  </div>
</div>

<?php require('partial/footer.php')?>