<?php
include("includes/init.php");
$title = "Not Found";
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title><?php echo $title; ?> </title>

  <link rel="stylesheet" type="text/css" href="/public/styles/site.css" media="all" />
</head>

<body>
  <?php include("includes/header.php"); ?>

  <main>
    <h1>OOPS!</h1>
    <h2 class="error">Page <?php echo $title; ?></h2>
    <p class="error">We can't seem to find the page you're looking for (404 error). </p>
    <p class="error">Try clicking "home" to get back to the reviews!</p>
  </main>

</body>

</html>
