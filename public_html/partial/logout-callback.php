<?php require('partial/header.php')?>

  <h2>
    OpenID Connect - Logout Callback
    <div class="btn-group  pull-right" role="group">
      <a class="btn btn-default" href="http://guide.docs.7pass.ctf.prosiebensat1.com/api/index.html#api-OpenID_Connect-OpenIDAuthorize" target="_blank" role="button">Documentation</a>
      <a class="btn btn-success" href="/" role="button">Continue</a>
    </div>
  </h2>

  <div class="panel panel-primary">
    <div class="panel-heading">
      <h3 class="panel-title">Code</h3>
    </div>
    <div class="panel-body">

<pre>
//remove your authentication data from the session
unset($_SESSION['tokens']);
</pre>

    </div>
  </div>


<?php require('partial/footer.php')?>