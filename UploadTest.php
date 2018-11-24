<?php

require_once "UploadClass.php";
if (isset($_POST['submit-file'])){
    $file = new Upload('img-file');
    if($file -> checkImg(1024000)){
        $file -> saveImg(300, 300);
        echo "successfully uploaded!";
    } else
        echo $file->getErrorMsg();
} 
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