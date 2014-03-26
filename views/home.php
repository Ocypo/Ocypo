<?php
# Key values of array passed to view are names of the string.
# In this case you are able to use $key since we defined it in the license class.
#
# It is also possible to use $args to get all arguments passed to the view.
# This could be quite usefull when you want to pass them through via json.
?>
<html>
<head>
  <title><?php echo VIEW::getTitle(); ?></title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="copyright" content="NielsMeijer.eu" />
</head>
<body>
<?php echo $text; ?>
</body>
</html>