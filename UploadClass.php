<?php
class  Upload
{ 
    private $fileName;
    private $fileType;
    private $fileTmpName;
    private $fileError;
    private $fileSize;

    private $fileExt;
    private $fileNameNew;
    private $destination;
    private $errorMsg="";
   
    public function __construct($uploadFieldName) #input is an array(input tag name attribute)
    {
        $this -> fileName = $_FILES[$uploadFieldName]['name'];
        $this -> fileType = $_FILES[$uploadFieldName]['type'];
        $this -> fileTmpName = $_FILES[$uploadFieldName]['tmp_name'];
        $this -> fileError = $_FILES[$uploadFieldName]['error'];
        $this -> fileSize = $_FILES[$uploadFieldName]['size'];
    }

    public function checkImg($maxSize=1024000)
    {
        #checks: size & extension & upload errors & empty upload
        if ($this -> fileSize == 0){
            $this -> errorMsg = "there is no file for upload";
            return false;
        }
        $fileExtTmp = explode(".", $this -> fileName);
        $this -> fileExt = strtolower(end($fileExtTmp));
        if (in_array($this -> fileExt, ['jpg', 'jpeg', 'png'])){
            if ($this -> fileError === 0){
                if ($this -> fileSize < $maxSize){
                    $this -> errorMsg = null;
                    return true;
                } else {
                    $this -> errorMsg = "your file is too big for that!";
                    return false;
                }
            } else {
                $this -> errorMsg = "there was an error on uploading your file! (error code = $this->fileError)";
                return false; 
            }
        } else {
            $this -> errorMsg = "you can not upload files of this type!";
            return false;
        }
    }

    public function saveImg($newWidth, $newHeight, $to='img/200x200/', $aspectRatioEnabled=false, $quality=100)
    {
        # create new img - sets(fileNameNew & destination)
        switch ($this -> fileType){ #get image from base upload dir to memory
            case "image/png":
                $img = imagecreatefrompng($this -> fileTmpName);
                break;
            case "image/gif":
                $img = imagecreatefromgif($this -> fileTmpName);
                break;
            default:
                $img = imagecreatefromjpeg($this -> fileTmpName);
                break;
        }
        list($oldWidth, $oldHeight) = getimagesize($this -> fileTmpName); #get width & height of main image
        $tmpImg = imagecreatetruecolor($newWidth, $newHeight); #create the free plane for resized image
        $white = imagecolorallocate($tmpImg, 255, 255, 255);
        imagecopyresampled($tmpImg, $img, 0, 0, 0, 0, $newWidth, $newHeight, $oldWidth, $oldHeight);
        $fileNameNew = time().".".rand().".".($this -> fileExt);
        if (imagejpeg($tmpImg, $to.$fileNameNew, $quality)){
            $this -> destination = $to.($this -> fileNameNew);
            imagedestroy($tmpImg);
            imagedestroy($img);
            return true;
        } else
            return ("Image not Resized!"); 
    }

    public function getImgPath()
    {
        return $this -> destination;
    }

    public function getImgName()
    {
        return $this -> fileNameNew;
    }

    public function getErrorMsg()
    {
        return $this -> errorMsg;
    }

}