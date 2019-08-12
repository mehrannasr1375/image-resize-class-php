<?php
require_once "./image.class.php";


// made a new instance od Image class
$image = new Image('1.jpg');


// check extension, size, resolution, error
$check_res  = $image->check(); //true || false
$resize_res = $image->resize(200,200,'crop'); //null
$save_res   = $image->saveImage('./result.jpg',100); //null


var_dump($image);
var_dump($check_res);
















?>
