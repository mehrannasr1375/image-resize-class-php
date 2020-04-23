<?php
require_once "./image.class.php";

if (isset($_POST['submit'])) {
    $image = new Image('image'); // input name=image

    if ($image->check(2048)) {
        $img->resize(1024, 768, 'crop');
        $image->saveTo('./', 100);
    }  
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="image">
    <button type="submit" name="submit">send</button>
</form>

