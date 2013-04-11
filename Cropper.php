<?php

namespace EasyE;

require_once 'Exception.php';
require_once 'AbstractImageProcessor.php';

class Cropper extends AbstractImageProcessor
{
    const CROP_LOCATION_RIGHT = 'right';
    const CROP_LOCATION_CENTER = 'center';
    const CROP_LOCATION_LEFT = 'left';
    
    const CROP_RESULT_SUCCESS = 'crop_success';
    const CROP_RESULT_ALREADY_SQUARE = 'crop_already_square';

    private $_sourceFileLocation = null;
    private $_newFileLocation = null;
    private $_croppedFilePermissions = 0755;
    private $_cropQuality = 100;

    public function __construct($sourceFileLocation = null, 
                                    $newFileLocation = null)
    {
        parent::__construct();

        if (!is_null($sourceFileLocation)){
            $this->setSourceFileLocation($sourceFileLocation);
        }

        if (!is_null($newFileLocation)){
            $this->setNewFileLocation($newFileLocation);
        }
    }

    public function setSourceFileLocation($fileLocation)
    {
        if( (!is_file($fileLocation)) || (!file_exists($fileLocation)) ){
            throw new Exception('Source file does not exist.');
        }elseif(!is_readable($fileLocation)){
            throw new Exception('Source file is not readable.');
        }

        $this->_sourceFileLocation = $fileLocation;

        return $this;
    }

    public function setNewFileLocation($fileLocation)
    {
        $dir = dirname($fileLocation);

        if(!is_dir($dir) ){
            throw new Exception('New file directory (' . $dir . ') does not exist.');
        }elseif(!is_writable($dir)){
            throw new Exception('New file directory (' . $dir . ') is not writable.');
        }

        $this->_newFileLocation = $fileLocation;
        
        return $this;
    }

    public function getSourceFileLocation()
    {
        if (is_null($this->_sourceFileLocation)){
            throw new Exception('Source file location not set');
        }

        return $this->_sourceFileLocation;
    }

    public function getNewFileLocation()
    {
        return $this->_newFileLocation;
    }

    public function cropToSquare($location = 'center')
    {
        $sourceFile     = $this->getSourceFileLocation();
        $newFile        = $this->getNewFileLocation();

        $info = GetImageSize($sourceFile);

        $width      = (int)$info[0];
        $height     = (int)$info[1];
        $mime       = $info['mime'];
        
        if ($width == $height){
            
            // The source image is already a square, return result.
            return array('result' => self::CROP_RESULT_ALREADY_SQUARE,
                            'filePath' => $sourceFile,
                            'height' => $height,
                            'width' => $width);
            
        }else{
        
            // What sort of image are we cropping?
            $type = substr(strrchr($mime, '/'), 1);

            switch ($type){
                case 'jpeg':
                    $imageCreateFunc = 'ImageCreateFromJPEG';
                    $imageSaveFunc = 'ImageJPEG';
                    $newImageExt = 'jpg';
                    break;

                case 'png':
                    $imageCreateFunc = 'ImageCreateFromPNG';
                    $imageSaveFunc = 'ImagePNG';
                    $newImageExt = 'png';
                    break;

                case 'bmp':
                    $imageCreateFunc = 'ImageCreateFromBMP';
                    $imageSaveFunc = 'ImageBMP';
                    $newImageExt = 'bmp';
                    break;

                case 'gif':
                    $imageCreateFunc = 'ImageCreateFromGIF';
                    $imageSaveFunc = 'ImageGIF';
                    $newImageExt = 'gif';
                    break;

                case 'vnd.wap.wbmp':
                    $imageCreateFunc = 'ImageCreateFromWBMP';
                    $imageSaveFunc = 'ImageWBMP';
                    $newImageExt = 'bmp';
                    break;

                case 'xbm':
                    $imageCreateFunc = 'ImageCreateFromXBM';
                    $imageSaveFunc = 'ImageXBM';
                    $newImageExt = 'xbm';
                    break;

                default:
                    $imageCreateFunc = 'ImageCreateFromJPEG';
                    $imageSaveFunc = 'ImageJPEG';
                    $newImageExt = 'jpg';
            }

            // Calculate the coordinates.

            // Horizontal Rectangle?
            if($width > $height){

               if($location == 'center'){

                   $xPos = ($width - $height) / 2;
                   $xPos = ceil($xPos);

                   $yPos = 0;

               }else if($location == 'left'){

                   $xPos = 0;
                   $yPos = 0;

               }else if($location == 'right'){

                   $xPos = ($width - $height);
                   $yPos = 0;
               }

               $newWidth = $height;
               $newHeight = $height;
                
            // Vertical Rectangle?
            }else if($height > $width){

                if($location == 'center'){

                    $xPos = 0;

                    $yPos = ($height - $width) / 2;
                    $yPos = ceil($yPos);

                }else if($location == 'left'){

                    $xPos = 0;
                    $yPos = 0;

                }else if($location == 'right'){

                    $xPos = 0;
                    $yPos = ($height - $width);

                }

                $newWidth = $width;
                $newHeight = $width;
            }

            $image = $imageCreateFunc($sourceFile);
            
            if (!$image){
                throw new Exception('Could not create image using ' . $imageCreateFunc);
            }

            $newImage = ImageCreateTrueColor($newWidth, $newHeight);

            // Crop to Square using the given dimensions
            $copyRes = imagecopyresampled($newImage, 
                                            $image, 
                                            0, 
                                            0, 
                                            $xPos, 
                                            $yPos, 
                                            $width, 
                                            $height, 
                                            $width, 
                                            $height);

            if (!$copyRes){
                throw new Exception('Could not crop image using imagecopyresampled()');
            }
                
            if(is_null($newFile)){

                $filename = $sourceFile;

                if (!is_writable($filename)){
                    throw new Exception('Insufficient privileges to write to ' . $filename);
                }

            }else{

                $filename = $newFile;

            }

            // Save image 
            $process = $imageSaveFunc($newImage, $filename, $this->_cropQuality);
            
            if (!$process){
                throw new Exception('There was a problem saving the cropped image.');
            }

            // Set permissions of the cropped image.
            chmod($filename, $this->_croppedFilePermissions);

            // Return result.
            return array('result' => self::CROP_RESULT_SUCCESS,
                            'filePath' => $filename,
                            'height' => $newHeight,
                            'width' => $newWidth);
        }
    }
}