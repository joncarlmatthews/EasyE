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
    const CROP_RESULT_NOTHING_TO_DO = 'destination is already at required dimensions';

    private $_sourceFileLocation = null;
    private $_destinationFileLocation = null;
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
            $this->setDestinationFileLocation($newFileLocation);
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

    public function setDestinationFileLocation($fileLocation)
    {
        $dir = dirname($fileLocation);

        if(!is_dir($dir) ){
            throw new Exception('New file directory (' . $dir . ') does not exist.');
        }elseif(!is_writable($dir)){
            throw new Exception('New file directory (' . $dir . ') is not writable.');
        }

        $this->_destinationFileLocation = $fileLocation;
        
        return $this;
    }

    public function getSourceFileLocation()
    {
        if (is_null($this->_sourceFileLocation)){
            throw new Exception('Source file location not set');
        }

        return $this->_sourceFileLocation;
    }

    public function getDestinationFileLocation()
    {
        return $this->_destinationFileLocation;
    }

    public function cropToSquare($location = 'center')
    {
        $sourceFileLocation = $this->getSourceFileLocation();

        $sourceFileInfo     = GetImageSize($sourceFileLocation);
        $sourceFileWidth    = (int)$sourceFileInfo[0];
        $sourceFileHeight   = (int)$sourceFileInfo[1];
        $sourceFileMime     = pathinfo($sourceFileLocation, PATHINFO_EXTENSION);

        $newFileLocation = $this->getDestinationFileLocation();

        // What sort of image are we cropping?         
        switch ($sourceFileMime){
            case 'jpg':
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
                throw new Exception('Mime type ' . $sourceFileMime . 'not supported.');
                break;
        }

        // Calculate the coordinates.

        // Does the destination file already exists?
        if (is_file($newFileLocation)){

            //if ( ($sourceFileWidth == $sourceFileHeight){

            $destinationFileInfo     = GetImageSize($destinationFileLocation);
            $destinationFileWidth    = (int)$destinationFileInfo[0];
            $destinationFileHeight   = (int)$destinationFileInfo[1];
            $destinationFileMime     = pathinfo($destinationFileInfo, PATHINFO_EXTENSION);

            if ($destinationFileLocation == $newFileLocation){

            }

            // The source image is already a square, return result.
            return array('result' => self::CROP_RESULT_NOTHING_TO_DO,
                            'sourceFilePath' => $sourceFileLocation,
                            'sourceFileHeight' => $sourceFileHeight,
                            'sourceFileWidth' => $sourceFileWidth
                            'destinationFilePath' => $sourceFileLocation,
                            'destinationFileHeight' => $sourceFileHeight,
                            'destinationFileWidth' => $sourceFileWidth
                            );

        // Horizontal Rectangle?
        }elseif($sourceFileWidth > $sourceFileHeight){

           if ($location == 'center'){

               $xPos = ($sourceFileWidth - $sourceFileHeight) / 2;
               $xPos = ceil($xPos);

               $yPos = 0;

           }elseif($location == 'left'){

               $xPos = 0;
               $yPos = 0;

           }elseif($location == 'right'){

               $xPos = ($sourceFileWidth - $sourceFileHeight);
               $yPos = 0;
           }

           $newWidth = $sourceFileHeight;
           $newHeight = $sourceFileHeight;
            
        // Vertical Rectangle?
        }elseif($sourceFileHeight > $sourceFileWidth){

            if($location == 'center'){

                $xPos = 0;

                $yPos = ($sourceFileHeight - $sourceFileWidth) / 2;
                $yPos = ceil($yPos);

            }elseif($location == 'left'){

                $xPos = 0;
                $yPos = 0;

            }elseif($location == 'right'){

                $xPos = 0;
                $yPos = ($sourceFileHeight - $sourceFileWidth);

            }

            $newWidth = $sourceFileWidth;
            $newHeight = $sourceFileWidth;
        }

        $image = $imageCreateFunc($sourceFileLocation);
        
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
                                        $sourceFileWidth, 
                                        $sourceFileHeight, 
                                        $sourceFileWidth, 
                                        $sourceFileHeight);

        if (!$copyRes){
            throw new Exception('Could not crop image using imagecopyresampled()');
        }
            
        if(is_null($sourceFileLocation)){

            $filename = $sourceFileLocation;

            if (!is_writable($filename)){
                throw new Exception('Insufficient privileges to write to ' . $filename);
            }

        }else{

            $filename = $sourceFileLocation;

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
                        'sourceFileHeight' => $newHeight,
                        'sourceFileWidth' => $newWidth);

    }
}