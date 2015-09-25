<?php require('partial/header.php')?>

<h1>Exception</h1>
<pre>
<?=get_class($exception)?> - <?=$exception->getMessage()?>
</pre>

<?php require('partial/footer.php')?>