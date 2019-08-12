<?php

class Image {

    /* This class takes only 1 argument:
     *              file path with file name in a string
     *              that it can be the temporary path of uploaded image (like $_FILES['fieldname']['tmp_name])
     */
    
    private $image; // our opened image, known as a resource in an attribute
    private $width;
    private $height;
    public  $error = '';
    private $fileSize; //in KiloBytes
    private $extension;

    private $imageResized; //result as a resource is accessible on this attribute (image on a variable)

    
    
    function __construct($fileName)
    {


        // get the extension of image
        $this->extension = @strtolower(end(explode(".", $fileName)));


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
        if ( empty($this->error) ) 
        {
            if ( $this->fileSize < $maxSize && $this->fileSize != 0 ) {
                $this->error = null;
                return true;
            }
            else if ( $this->fileSize == 0 ) {
                $this->error = "Empty File";
                return false;
            } 
            else {
                $this->error = "Large File";
                return false;
            }
        } 
        else 
            return false;  
    }


    public function resize($newWidth, $newHeight, $option="auto")
    { 
        // Get optimal width and height - based on $option
        $optimalSizes  = $this->getOptimalDimensions($newWidth, $newHeight, strtolower($option));
        $optimalWidth  = $optimalSizes['optimalWidth']; 
        $optimalHeight = $optimalSizes['optimalHeight'];

        // Resample - create image canvas of x, y size
        $this->imageResized = imagecreatetruecolor($optimalWidth, $optimalHeight);
        imagecopyresampled($this->imageResized, $this->image, 0, 0, 0, 0, $optimalWidth, $optimalHeight, $this->width, $this->height);

        // if option is 'crop', then crop too
        if ($option == 'crop') 
            $this->crop($optimalWidth, $optimalHeight, $newWidth, $newHeight);
    }


    private function getOptimalDimensions($newWidth, $newHeight, $option) // choose optional size by selected option
    {
        switch ($option)
        {
            case 'exact':
                $optimalWidth = $newWidth;
                $optimalHeight= $newHeight;
                break;
            case 'portrait':
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
                break;
            case 'landscape':
                $optimalWidth = $newWidth;
                $optimalHeight= $this->getSizeByFixedWidth($newWidth);
                break;
            case 'auto':
                $optionArray = $this->getSizeByAuto($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
            case 'crop':
                $optionArray = $this->getOptimalCrop($newWidth, $newHeight);
                $optimalWidth = $optionArray['optimalWidth'];
                $optimalHeight = $optionArray['optimalHeight'];
                break;
        }
        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }


    private function getSizeByFixedHeight($newHeight)
    {
        $ratio = $this->width / $this->height;
        $newWidth = $newHeight * $ratio;
        return $newWidth;
    }


    private function getSizeByFixedWidth($newWidth)
    {
        $ratio = $this->height / $this->width;
        $newHeight = $newWidth * $ratio;
        return $newHeight;
    }


    private function getSizeByAuto($newWidth, $newHeight)
    {
        if ($this->height < $this->width)// Image to be resized is wider (landscape)
        {
            $optimalWidth = $newWidth;
            $optimalHeight= $this->getSizeByFixedWidth($newWidth);
        }
        elseif ($this->height > $this->width)// Image to be resized is taller (portrait)
        {
            $optimalWidth = $this->getSizeByFixedHeight($newHeight);
            $optimalHeight= $newHeight;
        }
        else// Image to be resizerd is a square
        {
            if ($newHeight < $newWidth) {
                $optimalWidth = $newWidth;
                $optimalHeight= $this->getSizeByFixedWidth($newWidth);
            } else if ($newHeight > $newWidth) {
                $optimalWidth = $this->getSizeByFixedHeight($newHeight);
                $optimalHeight= $newHeight;
            } else {
                // *** Sqaure being resized to a square
                $optimalWidth = $newWidth;
                $optimalHeight= $newHeight;
            }
        }

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }

    
    private function getOptimalCrop($newWidth, $newHeight)
    {

        $heightRatio = $this->height / $newHeight;
        $widthRatio  = $this->width /  $newWidth;

        if ($heightRatio < $widthRatio) {
            $optimalRatio = $heightRatio;
        } else {
            $optimalRatio = $widthRatio;
        }

        $optimalHeight = $this->height / $optimalRatio;
        $optimalWidth  = $this->width  / $optimalRatio;

        return array('optimalWidth' => $optimalWidth, 'optimalHeight' => $optimalHeight);
    }


    private function crop($optimalWidth, $optimalHeight, $newWidth, $newHeight)
    {
        try {
            // Find center - this will be used for the strart point for crop
            $cropStartX = ( $optimalWidth / 2) - ( $newWidth /2 );
            $cropStartY = ( $optimalHeight/ 2) - ( $newHeight/2 );

            $crop = $this->imageResized;
            // imagedestroy($this->imageResized);

            // crop from center to exact requested size
            $this->imageResized = imagecreatetruecolor($newWidth , $newHeight);
            imagecopyresampled($this->imageResized, $crop , 0, 0, $cropStartX, $cropStartY, $newWidth, $newHeight , $newWidth, $newHeight);
        }
        catch (Exception $e) {
            $this->error = 'cropError : '.$e->getMessage();
        }
    }


    public function saveImage($savePath, $imageQuality="100")
    {
        $extension = strtolower(strrchr($savePath, '.'));
        switch( $extension )
        {
            case '.jpg':
            case '.jpeg':
                imagejpeg($this->imageResized, $savePath, $imageQuality);
                break;
            case '.gif':
                imagegif($this->imageResized, $savePath);
                break;
            case '.png':
                // Scale quality from 0-100 to 0-9
                $scaleQuality = round(($imageQuality/100) * 9);
                // Invert quality setting as 0 is best, not 9
                $invertScaleQuality = 9 - $scaleQuality;
                imagepng($this->imageResized, $savePath, $invertScaleQuality);
                break;
            default:// No extension => No save
                    $this->error = 'saveError';
                break;
        }
        imagedestroy($this->imageResized);
    }


}
