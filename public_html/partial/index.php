<?php require('partial/header.php')?>

<h2>
  7Pass PHP SDK Example Application
  <div class="btn-group  pull-right" role="group">
    <a class="btn btn-default" href="https://github.com/p7s1-ctf/7pass-php-sdk" target="_blank" role="button">Documentation</a>
  </div>
</h2>

<div class="panel panel-primary">
  <div class="panel-heading">
    <h3 class="panel-title">Code</h3>
  </div>
  <div class="panel-body">

<pre>
//creates configuration
$ssoConfig = new P7\SSO\Configuration($config);

//if you need you can set a custom cache driver
$cacheDriver = new Stash\Driver\FileSystem();
$ssoConfig->setCachePool(new Stash\Pool($cacheDriver));

//creates SSO object
$sso = new P7\SSO($ssoConfig);
</pre>

    </div>
  </div>

<?php require('partial/footer.php')?>