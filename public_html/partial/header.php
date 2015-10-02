<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>7Pass PHP SDK demo application</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
  <script src="https://cdn.rawgit.com/google/code-prettify/master/loader/run_prettify.js"></script>
</head>
<body>
  <!-- Static navbar -->
  <nav class="navbar navbar-default navbar-static-top">
    <div class="container">
      <div class="navbar-header">
        <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
          <span class="sr-only">Toggle navigation</span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
          <span class="icon-bar"></span>
        </button>
        <a class="navbar-brand" href="/">7Pass PHP SDK</a>
      </div>
      <div id="navbar" class="navbar-collapse collapse">
        <div class="navbar-right">
          <?php if(!$GLOBALS['loggedIn'] && $GLOBALS['action'] === 'index'):?><a href="/login" class="btn btn-success navbar-btn">Login</a></li><?php endif?>
          <?php if($GLOBALS['loggedIn']):?><a href="/account" class="btn btn-default navbar-btn">Account APIs</a><?php endif?>
          <?php if($GLOBALS['loggedIn']):?><a href="/logout" class="btn btn-danger navbar-btn">Logout</a><?php endif?>
        </div>
      </div>
    </div>
  </nav>

  <div class="container">