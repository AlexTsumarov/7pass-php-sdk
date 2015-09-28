<?php require('partial/header.php')?>

<h2>
  7Pass PHP SDK Example Application
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="https://github.com/p7s1-ctf/7pass-php-sdk" target="_blank" role="button">Documentation</a>
  </div>
</h2>

<p>
  This sample application was developed to show you how 7Pass PHP SDK can be used to authenticate users on your site against 7Pass SSO provider and
  how you can make 7Pass API calls on behalf this authenticated user subsequently.
</p>

<div class="well">
  To obtain your 'client_id' and 'client_secret' keys please contact our 7Pass SSO team or Filip Skokan at <a href="maito:filip.skokan@prosiebensat1.com">filip.skokan@prosiebensat1.com</a>.
</div>

<?php if(!$loggedIn):?>
  <p>
    <b>To start the demonstration please use Login button above.</b>
  </p>
<?php endif?>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">
    <p>
      In order to set up this SDK you pass a configuration which is specific just to your application.
      This includes the following:
      <ul>
        <li><b>client_id</b> (required)</li>
        <li><b>client_secret</b> (required)</li>
        <li><b>environment</b> (optional) - defaults to: production; Available options: 'production', 'qa'</li>
      </ul>
    </p>
    <p>
      We have got two environments running: 'production' and 'qa'.
      'qa' environment is available for you for an intergration and testing purposes. Once you feel everything works as expected and
      you are ready to live with your site, you just need to switch to 'production' environment providing production 'client_id' and 'client_secret'.
    </p>
<pre class="prettyprint">
$config = [
  'client_id' => 'YOUR_CLIENT_ID',
  'client_secret' => 'YOUR_CLIENT_SECRET',
  'environment' => 'qa' //default: 'production'
];

//creates configuration
$ssoConfig = new P7\SSO\Configuration($config);

//creates SSO object
$sso = new P7\SSO($ssoConfig);
</pre>

    </div>
  </div>

<?php require('partial/footer.php')?>