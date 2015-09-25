<?php require('partial/header.php')?>

<h2>
  Account details
</h2>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">
<pre>
$accountClient = $sso->accountClient($tokens->access_token);

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