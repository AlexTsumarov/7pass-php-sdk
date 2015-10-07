<?php require('partial/header.php')?>

<h2>
  7Pass PHP SDK Example Application
  <div class="btn-group pull-right" role="group">
    <a class="btn btn-default" href="https://github.com/p7s1-ctf/7pass-php-sdk" target="_blank" role="button">Documentation</a>
  </div>
</h2>

<p>
  The 7Pass PHP SDK library allows you to authenticate users against
  the 7Pass SSO provider and provides utility methods for working with
  the available user related APIs.

  The purpose of the following example is to demonstrate the usage of
  the library and show the interaction between the SDK and the 7Pass
  SSO service.
</p>

<p>
  To use the library, you need to have a valid client. The client
  represents the entity which is associated with a service you want to
  authenticate to. To obtain the client credentials, you first need to
  contact the 7Pass SSO team or contact Filip Skokan directly
  at <a href="mailto:filip.skokan@prosiebensat1.com?subject=7Pass Dev
  Account" target="_blank">filip.skokan@prosiebensat1.com</a>.
</p>


<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">
    <p>
      To use the library, it's necessary to initialize it with the
      credentials of the client we want to use. If you don't have the
      credentials yet, please see above.

      <ul>
        <li><b>client_id</b> (required)</li>
        <li><b>client_secret</b> (required)</li>
      </ul>
    </p>
    <p>
      If you're starting the development, it's always a good idea to
      work against a non live instance of the 7Pass SSO service. To
      specify the instance (the environment) against which you want to
      issue the requests, you can pass an additional key
      called <b>environment</b> to the configuration. There're
      currently two environments running: QA and production. Don't
      forget to switch to the production version before you release
      your application to the public.
    </p>
<pre class="prettyprint">
$config = [
  'client_id' => 'YOUR_CLIENT_ID',
  'client_secret' => 'YOUR_CLIENT_SECRET',
  'environment' => 'qa' // Optional, defaults to 'production'
];

// Creates the configuration object
$ssoConfig = new P7\SSO\Configuration($config);

// Pass the configuration to the SSO object
$sso = new P7\SSO($ssoConfig);
</pre>

    </div>
  </div>

<?php if(!$loggedIn):?>
  <p>
    Once the library is set up, we can proceed to authenticate a user.
    <b>To start the demonstration, please use the Login button above.</b>
  </p>
<?php endif?>

<?php require('partial/footer.php')?>
