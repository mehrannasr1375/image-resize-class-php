<?php


include "./newImgClass.php";

$image = new Image('1.jpg');
$res = $image->check();
$res = $image->resize(200,200,'crop');
$res = $image->saveImage('./aa.jpg',100);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
</head>
<body>


	<!-- upload form -->
    <form method="POST" enctype="multipart/form-data">
      <input type="file" name="img-file" id="">
      <input type="submit" name="submit-file" value="submit">
    </form>
    
	

</body>
</html>
