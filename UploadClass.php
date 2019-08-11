<?php

class Image {

    /* This class takes only 1 argument:
     *              file path with file name in a string
     *              that it can be the temporary path of uploaded image
     */
    
    private $image; // our opened image, known as a resource in an attribute
    private $width;
    private $height;
    public  $error = '';
    private $fileSize; //in Bytes
    private $extension;

    private $imageResized; //result as a resource is accessible on this attribute (image on a variable)

    
    
    function __construct($fileName)
    {


        // get the extension of image
        $this->extension = strtolower(end(explode(".", $fileName)));


        // get image from specified path & store it into image attribute of class
        switch ( $this->extension ) {
            case 'jpeg':
            case 'jpg':
                $this->image = imagecreatefromjpeg($fileName);
                break;
            case 'png':
                $this->image = imagecreatefrompng($fileName);
                break;
            case 'gif':
                $this->image = imagecreatefromgif($fileName);
                break;
            default:
                $this->image = null;
                $this->error = 'Unsupported Image Type';
                break;
        }


        /* get with & height & fileSize & store them into class attributes,
         if there is no error on reading that */
        if ( empty($this->error) ) {
            /* get size with 'GD' (from a variable in memory):
               $this->width  = imagesx($this->image);
               $this->height = imagesy($this->image); */

            // get image size from path && set error if size<10kb
            $this->fileSize = @filesize($fileName)/1024; //KBytes
            if ( $this->fileSize<10 )
                $this->error = "Very Small File";

            // get width and height from path && set error if width<128 || height<128
            list($this->width, $this->height) = @getimagesize($fileName); 
            if ( $this->width < 128 || $this->height < 128 )
                $this->error = "Very Low Resolution";

        }

    }


    public function check($maxSize=1024) // 1 KBytes - returns boolean
    { 
        // check upload errors & empty file & fileSize
        if ( empty($this->error) ) {
            if ( $this->fileSize < $maxSize && $this->fileSize != 0 ) {
                $this->error = null;
                return true;
            } else if ( $this->fileSize == 0 ) {
                $this->error = "empty file";
                return false;
            } else {
                $this->error = "large file";
                return false;
            }
        } else 
            return false;  
    }


    





















}
