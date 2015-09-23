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
    const CROP_RESULT_COPY = 'image copied';

    private $_croppedFilePermissions = 0755;
    private $_cropQuality = 100;

    private $_sourceFileWidth = 0;
    private $_sourceFileHeight = 0;

    private $_createMethod = null;
    private $_saveMethod = null;
    private $_newImageHeight = null;
    private $_newImageWidth = null;
    private $_newImageExt = null;
    private $_xPos = null;
    private $_yPos = null;

    public function setQuality($value)
    {
        $this->_cropQuality = (int)$value;

        return $this;
    }

    public function setFilePermissions($octal)
    {
        $this->_croppedFilePermissions = $octal;

        return $this;
    }

    public function cropToSquare($location = self::CROP_LOCATION_CENTER)
    {
        $sourceFileLocation = $this->getSourceFileLocation();

        $sourceFileInfo     = GetImageSize($sourceFileLocation);
        $sourceFileWidth    = (int)$sourceFileInfo[0];
        $sourceFileHeight   = (int)$sourceFileInfo[1];
        $sourceFileMime     = pathinfo($sourceFileLocation, PATHINFO_EXTENSION);

        // What sort of image are we cropping?         
        switch ($sourceFileMime){
            case 'jpg':
            case 'jpeg':
                $this->_createMethod = self::CREATE_METHOD_JPEG;
                break;

            case 'png':
                $this->_createMethod = self::CREATE_METHOD_PNG;
                break;

            case 'bmp':
                $this->_createMethod = self::CREATE_METHOD_BMP;
                break;

            case 'gif':
                $this->_createMethod = self::CREATE_METHOD_GIF;
                break;

            default:
                throw new Exception('Mime type ' 
                                        . $sourceFileMime 
                                        . 'not supported for image creation.');
                break;
        }

        // What sort of image are we saving?
        $destinationFileLocation = $this->getDestinationFileLocation();
        $destinationFileMime     = pathinfo($destinationFileLocation, PATHINFO_EXTENSION);

        switch ($destinationFileMime){
            case 'jpg':
            case 'jpeg':
                $this->_saveMethod = self::SAVE_METHOD_JPEG;
                $this->_newImageExt = self::FILE_EXT_JPEG;
                break;

            case 'png':
                $this->_saveMethod = self::SAVE_METHOD_PNG;
                $this->_newImageExt = self::FILE_EXT_PNG;
                break;

            case 'bmp':
                $this->_saveMethod = self::SAVE_METHOD_BMP;
                $this->_newImageExt = self::FILE_EXT_BMP;
                break;

            case 'gif':
                $this->_saveMethod = self::SAVE_METHOD_GIF;
                $this->_newImageExt = self::FILE_EXT_GIF;
                break;

            default:
                throw new Exception('Mime type ' 
                                        . $destinationFileMime 
                                        . 'not supported for image saving.');
                break;
        }

        // Does the destination file already exists?
        if (is_file($destinationFileLocation)){

            // Is the destinatin file the same as the source file?
            if ($this->getSourceFileLocation() == $this->getDestinationFileLocation()){

                // Is the source file already square?
                if( ($sourceFileHeight == $sourceFileWidth) ){

                    // There's nothing to do.
                    return array('result' => self::CROP_RESULT_NOTHING_TO_DO,
                            'sourceFilePath' => $sourceFileLocation,
                            'sourceFileHeight' => $sourceFileHeight,
                            'sourceFileWidth' => $sourceFileWidth,
                            'destinationFilePath' => $sourceFileLocation,
                            'destinationFileHeight' => $sourceFileHeight,
                            'destinationFileWidth' => $sourceFileWidth
                            );


                }

            }else{

                // Remove the file to be replaced.
                $res = unlink($destinationFileLocation);

                if (!$res){
                    throw new Exception('Cannot remove file at destination');
                }

            }
        }

        // Are the dimensions of the source file already square?
        if( ($sourceFileHeight == $sourceFileWidth) ){

            // The source image is already a square, simply copy the image from
            // source to destination.
            $res = copy($this->getSourceFileLocation(), $this->getDestinationFileLocation());

            if (!$res){
                throw new Exception('Cannot copy file from source to destination');
            }

            return array('result' => self::CROP_RESULT_COPY,
                            'sourceFilePath' => $this->getSourceFileLocation(),
                            'sourceFileHeight' => $sourceFileHeight,
                            'sourceFileWidth' => $sourceFileWidth,
                            'destinationFilePath' => $this->getDestinationFileLocation(),
                            'destinationFileHeight' => $sourceFileHeight,
                            'destinationFileWidth' => $sourceFileWidth
                            );
        
        }elseif($sourceFileWidth > $sourceFileHeight){

            // ...Horizontal Rectangle

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

           $this->_newImageWidth = $sourceFileHeight;
           $this->_newImageHeight = $sourceFileHeight;
        
        }elseif($sourceFileHeight > $sourceFileWidth){

            // Vertical Rectangle.

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

            $this->_newImageWidth = $sourceFileWidth;
            $this->_newImageHeight = $sourceFileWidth;
        }

        // Perform crop...

        $this->_xPos = $xPos;
        $this->_yPos = $yPos;

        $this->_sourceFileWidth = $sourceFileWidth;
        $this->_sourceFileHeight = $sourceFileHeight;

        $imageCreateFunc    = strtolower($this->_createMethod);
        $imageSaveFunc      = strtolower($this->_saveMethod);

        $image = $imageCreateFunc($this->getSourceFileLocation());
        
        if (!$image){
            throw new Exception('Could not create image using ' . $imageCreateFunc);
        }

        $newImage = ImageCreateTrueColor($this->_newImageWidth, $this->_newImageHeight);

        // Crop to Square using the given dimensions
        $copyRes = imagecopyresampled($newImage, 
                                        $image, 
                                        0, 
                                        0, 
                                        $this->_xPos, 
                                        $this->_Pos, 
                                        $this->_sourceFileWidth, 
                                        $this->_sourceFileHeight, 
                                        $this->_sourceFileWidth, 
                                        $this->_sourceFileHeight);

        if (!$copyRes){
            throw new Exception('Could not crop image using imagecopyresampled()');
        }

        // Save image 
        $process = $imageSaveFunc($newImage, $this->getDestinationFileLocation(), $this->_cropQuality);
        
        if (!$process){
            throw new Exception('There was a problem saving the cropped image.');
        }

        // Set permissions of the cropped image.
        @chmod($filename, $this->_croppedFilePermissions);

        // Extract the destination's height and width.
        $destinationFileInfo     = GetImageSize($this->getDestinationFileLocation());
        $destinationFileWidth    = (int)$destinationFileInfo[0];
        $destinationFileHeight   = (int)$destinationFileInfo[1];

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

}