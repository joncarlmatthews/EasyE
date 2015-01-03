<?php

/**
 * EasyE Resizer
 *
 * @link      https://github.com/joncarlmatthews/EasyE for the canonical source repository
 * @license   GNU General Public License
 *
 * File format: UNIX
 * File encoding: UTF8
 * File indentation: Spaces (4). No tabs
 */

namespace EasyE;

require_once 'Exception.php';
require_once 'AbstractImageProcessor.php';

/**
 * The Resizer class provides methods for resizing an image.
 * 
 * @author Jon Matthews <joncarlmatthews@gmail.com> 
 */
class Resizer extends AbstractImageProcessor
{
    /**
     * Flag for resize not required resolution.
     * 
     * @access public
     * @var string
     */
    const RESULT_RESIZE_NOT_REQUIRED = 'resize_not_required';

    /**
     * Flag for resize successful resolution.
     * 
     * @access public
     * @var string
     */
    const RESULT_RESIZE_SUCCESSFUL = 'resize_successful';

    /**
     * The maximum width that the image should be.
     * 
     * @access private
     * @var NULL|string
     */
    private $_maxWidth = null;

    /**
     * The maximum height that the image should be.
     * 
     * @access private
     * @var NULL|string
     */
    private $_maxHeight = null;

    /**
     * The quality (0 - 100) that the resized image should be saved at.
     * 
     * @access private
     * @var integer
     */
    private $_quality = 0;

    /**
     * Source file image attributes.
     * 
     * @access private
     * @var NULL|string
     */
    private $_sourceAttrs = null;
    
    /**
     * Source file image width.
     * 
     * @access private
     * @var NULL|string
     */
    private $_sourceWidth = null;

    /**
     * Source file image height.
     * 
     * @access private
     * @var NULL|string
     */
    private $_sourceHeight = null;

    /**
     * Destination file image attributes.
     * 
     * @access private
     * @var NULL|string
     */
    private $_destAttrs = null;
    
    /**
     * Destination file image width.
     * 
     * @access private
     * @var NULL|string
     */
    private $_destWidth = null;

    /**
     * Destination file image height.
     * 
     * @access private
     * @var NULL|string
     */
    private $_destHeight = null;
    
    /**
     * Resized image width.
     * 
     * @access private
     * @var NULL|string
     */
    private $_newWidth = null;

    /**
     * Resized image height.
     * 
     * @access private
     * @var NULL|string
     */
    private $_newHeight = null;
    
    /**
     * Takes an image (from a file path) and resizes it. The newly resized image
     * can then either be created in a different location, therefore maintainig 
     * the original file. Or can be created in the original location, therefore
     * overwriting the original file depending on the values passed to
     * ::setSourceFileLocation() and ::setDestinationFileLocation()
     *
     * @access public
     * @param integer   $maxWidth   The maximum height of the resized image
     * @param integer   $maxHeight  The maximum width of the resized image
     * @param integer   $quality    The quality of the image 
     */
    public function resize($maxWidth = 1000, 
                            $maxHeight = 1000, 
                            $quality = 100)
    {
        $this->_maxWidth    = $maxWidth;
        $this->_maxHeight   = $maxHeight;
        $this->_quality     = $quality;

        $this->_sourceAttrs     = getimagesize($this->getSourceFileLocation());
        $this->_sourceWidth     = $this->_sourceAttrs[0];
        $this->_sourceHeight    = $this->_sourceAttrs[1];

        if (is_file($this->getDestinationFileLocation()) && file_exists($this->getDestinationFileLocation())){
            $this->_destAttrs     = getimagesize($this->getDestinationFileLocation());
            $this->_destWidth     = $this->_destAttrs[0];
            $this->_destHeight    = $this->_destAttrs[1];
        }
        
        // Do we even need to resize the image!?
        if ($this->_sourceWidth <= $this->_maxWidth && $this->_sourceHeight <= $this->_maxHeight){

            return array(
                'result' => self::RESULT_RESIZE_NOT_REQUIRED,

                'sourceFilePath' => $this->getSourceFileLocation(),
                'sourceFileWidth' => $this->_sourceWidth,
                'sourceFileHeight' => $this->_sourceHeight,
                
                'destinationFilePath' => $this->getDestinationFileLocation(),
                'destinationFileWidth' => $this->_destWidth,
                'destinationFileHeight' => $this->_destHeight,
            );

        }
        
        $ratio = $this->_sourceWidth / $this->_maxWidth;
                
        // set the defaults:
        $newWidth   = intval($this->_sourceWidth);
        $newHeight  = intval($this->_sourceHeight);     
        
        if ($this->_sourceWidth > $this->_maxWidth) {
            $newWidth   = intval($this->_maxWidth);
            $newHeight  = intval($this->_sourceHeight / $ratio);
        }
        
        // once we've got the size width, is the height now small enough? 
        if ($newHeight > $this->_maxHeight) {
            // set a new ratio
            $ratio      = $newHeight / $this->_maxHeight;
            $newWidth   = intval($newWidth / $ratio);
            $newHeight  = intval($this->_maxHeight);
        }
        
        $this->_newWidth    = $newWidth;
        $this->_newHeight   = $newHeight;
        
        switch (strtolower($this->_sourceAttrs['mime'])) {
            case 'image/jpeg':
                $this->_createFromJpeg();
                break;
            case 'image/pjpeg':
                $this->_createFromPJpeg();
                break;
            case 'image/png':
                $this->_createFromPng();
                break;
            case 'image/gif':
                $this->_createFromGif();
                break;
        
            default:
                throw new Exception('Mime Type \'' . $this->_sourceAttrs['mime'] . '\' not supported');
                break;
        }
        
        return array(
            'result' => self::RESULT_RESIZE_SUCCESSFUL,

            'sourceFilePath' => $this->getSourceFileLocation(),
            'sourceFileWidth' => $this->_sourceHeight,
            'sourceFileHeight' => $this->_sourceWidth,
            
            'destinationFilePath' => $this->getDestinationFileLocation(),
            'destinationFileWidth' => $newWidth,
            'destinationFileHeight' => $newHeight,
        );
                
    }
    
    /**
     * Resizes images of type image/jpeg.
     *
     * @access public
     * @author Jon Matthews <joncarlmatthews@gmail.com>
     * @return void
     */
    private function _createFromJpeg()
    {
        $img = imagecreatefromjpeg($this->getSourceFileLocation());
        
        $new_img = imagecreatetruecolor($this->_newWidth, $this->_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            $this->_newWidth, 
                            $this->_newHeight, 
                            $this->_sourceWidth, 
                            $this->_sourceHeight);
                            
        $res = @imagejpeg($new_img, $this->getDestinationFileLocation(), $this->_quality);

        if (!$res){
            throw new Exception('imagejpeg failed. Check permission of file.');
        }
    }
    
    /**
     * Resizes images of type image/jpeg.
     *
     * @access private
     * @return void
     */
    private function _createFromPJpeg()
    {
        $img = imagecreatefromjpeg($this->getSourceFileLocation());
        
        imageinterlace($img, 1);
        
        $new_img = imagecreatetruecolor($this->_newWidth, $this->_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            $this->_newWidth, 
                            $this->_newHeight, 
                            $this->_sourceWidth, 
                            $this->_sourceHeight);
                            
        $res = @imagejpeg($new_img, $this->getDestinationFileLocation(), $this->_quality);

        if (!$res){
            throw new Exception('imagejpeg failed. Check permission of file.');
        }
    }
    
    /**
     * Resizes images of type image/png.
     *
     * @access private
     * @return void
     */
    private function _createFromPng()
    {
        $img = imagecreatefrompng($this->getSourceFileLocation());
        
        $new_img = imagecreatetruecolor($this->_newWidth, $this->_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            $this->_newWidth, 
                            $this->_newHeight, 
                            $this->_sourceWidth, 
                            $this->_sourceHeight);
                            
        $res = @imagepng($new_img, $this->getDestinationFileLocation());   

        if (!$res){
            throw new Exception('imagepng failed. Check permission of file.');
        }     
    }
    
    /**
     * Resizes images of type image/gif.
     *
     * @access private
     * @return void
     */
    private function _createFromGif()
    {
        $img = imagecreatefromgif($this->getSourceFileLocation());
        
        $new_img = imagecreatetruecolor($this->_newWidth, $this->_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            $this->_newWidth, 
                            $this->_newHeight, 
                            $this->_sourceWidth, 
                            $this->_sourceHeight);
                            
        $res = @imagegif($new_img, $this->getDestinationFileLocation());

        if (!$res){
            throw new Exception('imagegif failed. Check permission of file.');
        }
    }
}