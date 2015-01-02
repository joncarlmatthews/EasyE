<?php

namespace EasyE;

require_once 'Exception.php';
require_once 'AbstractImageProcessor.php';

class Cropper extends AbstractImageProcessor
{
    const CREATE_METHOD_JPEG    = 'ImageCreateFromJPEG';
    const CREATE_METHOD_PNG     = 'ImageCreateFromPNG';
    const CREATE_METHOD_BMP     = 'ImageCreateFromBMP';
    const CREATE_METHOD_GIF     = 'ImageCreateFromGIF';

    const SAVE_METHOD_JPEG    = 'ImageJPEG';
    const SAVE_METHOD_PNG     = 'ImagePNG';
    const SAVE_METHOD_BMP     = 'ImageBMP';
    const SAVE_METHOD_GIF     = 'ImageGIF';

    const FILE_EXT_JPEG    = 'jpg';
    const FILE_EXT_PNG     = 'png';
    const FILE_EXT_BMP     = 'bmp';
    const FILE_EXT_GIF     = 'gif';

    const CROP_LOCATION_RIGHT = 'right';
    const CROP_LOCATION_CENTER = 'center';
    const CROP_LOCATION_LEFT = 'left';
    
    const CROP_RESULT_SUCCESS = 'crop_success';
    const CROP_RESULT_NOTHING_TO_DO = 'destination is already at required dimensions';

    private $_croppedFilePermissions = 0755;
    private $_cropQuality = 100;

    private $_createMethod = null;
    private $_saveMethod = null;

    public function cropToSquare($location = self::CROP_LOCATION_CENTER)
    {
        $this->_preCropChecks();

        $sourceFileLocation = $this->getSourceFileLocation();

        $sourceFileInfo     = GetImageSize($sourceFileLocation);
        $sourceFileWidth    = (int)$sourceFileInfo[0];
        $sourceFileHeight   = (int)$sourceFileInfo[1];
        $sourceFileMime     = pathinfo($sourceFileLocation, PATHINFO_EXTENSION);

        // What sort of image are we cropping?         
        switch ($sourceFileMime){
            case 'jpg':
            case 'jpeg':
                $imageCreateFunc = 'ImageCreateFromJPEG';
                break;

            case 'png':
                $imageCreateFunc = 'ImageCreateFromPNG';
                break;

            case 'bmp':
                $imageCreateFunc = 'ImageCreateFromBMP';
                break;

            case 'gif':
                $imageCreateFunc = 'ImageCreateFromGIF';
                break;

            case 'vnd.wap.wbmp':
                $imageCreateFunc = 'ImageCreateFromWBMP';
                break;

            case 'xbm':
                $imageCreateFunc = 'ImageCreateFromXBM';
                break;

            default:
                throw new Exception('Mime type ' 
                                        . $sourceFileMime 
                                        . 'not supported for image creation.');
                break;
        }

        $destinationFileLocation = $this->getDestinationFileLocation();
        $destinationFileMime     = pathinfo($destinationFileLocation, PATHINFO_EXTENSION);

        // What sort of image are we saving?         
        switch ($destinationFileMime){
            case 'jpg':
            case 'jpeg':
                $imageSaveFunc = 'ImageJPEG';
                $newImageExt = 'jpg';
                break;

            case 'png':
                $imageSaveFunc = 'ImagePNG';
                $newImageExt = 'png';
                break;

            case 'bmp':
                $imageSaveFunc = 'ImageBMP';
                $newImageExt = 'bmp';
                break;

            case 'gif':
                $imageSaveFunc = 'ImageGIF';
                $newImageExt = 'gif';
                break;

            case 'vnd.wap.wbmp':
                $imageSaveFunc = 'ImageWBMP';
                $newImageExt = 'bmp';
                break;

            case 'xbm':
                $imageSaveFunc = 'ImageXBM';
                $newImageExt = 'xbm';
                break;

            default:
                throw new Exception('Mime type ' 
                                        . $destinationFileMime 
                                        . 'not supported for image saving.');
                break;
        }

        // Does the destination file already exists?
        if (is_file($destinationFileLocation)){

            $destinationFileInfo     = GetImageSize($destinationFileLocation);
            $destinationFileWidth    = (int)$destinationFileInfo[0];
            $destinationFileHeight   = (int)$destinationFileInfo[1];
            $destinationFileMime     = pathinfo($destinationFileInfo, PATHINFO_EXTENSION);

            // Are the dimensions of the desination file already square?
            if ($destinationFileWidth == $destinationFileHeight){

                // The source image is already a square, return result.
                return array('result' => self::CROP_RESULT_NOTHING_TO_DO,
                                'sourceFilePath' => $sourceFileLocation,
                                'sourceFileHeight' => $sourceFileHeight,
                                'sourceFileWidth' => $sourceFileWidth,
                                'destinationFilePath' => $sourceFileLocation,
                                'destinationFileHeight' => $sourceFileHeight,
                                'destinationFileWidth' => $sourceFileWidth
                                );
            }else{
                die('TODO overwrite destination with source.');
            }
        
        }

        // Are the dimensions of the source file already square?
        if($sourceFileHeight == $sourceFileWidth){

            // The source image is already a square, return result.
            return array('result' => self::CROP_RESULT_NOTHING_TO_DO,
                            'sourceFilePath' => $sourceFileLocation,
                            'sourceFileHeight' => $sourceFileHeight,
                            'sourceFileWidth' => $sourceFileWidth,
                            'destinationFilePath' => $sourceFileLocation,
                            'destinationFileHeight' => $sourceFileHeight,
                            'destinationFileWidth' => $sourceFileWidth
                            );

        // Calculate the coordinates.

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

        // Return result.
        return array('result' => self::CROP_RESULT_SUCCESS,
                            'sourceFilePath' => $sourceFileLocation,
                            'sourceFileHeight' => $sourceFileHeight,
                            'sourceFileWidth' => $sourceFileWidth,
                            'destinationFilePath' => $destinationFileLocation,
                            'destinationFileHeight' => $destinationFileHeight,
                            'destinationFileWidth' => $destinationFileWidth
                            );
    }

    private function _performCrop()
    {
        $image = $imageCreateFunc($this->getSourceFileLocation());
        
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
            
        if(is_null($this->getSourceFileLocation())){

            $filename = $this->getSourceFileLocation();

            if (!is_writable($filename)){
                throw new Exception('Insufficient privileges to write to ' . $filename);
            }

        }else{

            $filename = $this->getSourceFileLocation();

        }

        // Save image 
        $process = $imageSaveFunc($newImage, $filename, $this->_cropQuality);
        
        if (!$process){
            throw new Exception('There was a problem saving the cropped image.');
        }

        // Set permissions of the cropped image.
        chmod($filename, $this->_croppedFilePermissions);
    }

    private function _preCropChecks()
    {
        // Is writtable etc here
    }
}