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
    private $_maxwidth = null;

    /**
     * The maximum height that the image should be.
     * 
     * @access private
     * @var NULL|string
     */
    private $_maxheight = null;

    /**
     * The quality (0 - 100) that the resized image should be saved at.
     * 
     * @access private
     * @var integer
     */
    private $_quality = 0;
    
    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private $_newWidth = null;

    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private $_newHeight = null;
    
    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private $_actualWidth = null;

    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private $_actualHeight = null;
    
    /**
     * Takes an image (from a file path) and resizes it. The newly resized image
     * can then either be created in a different location, therefore maintainig 
     * the original file. Or can be created in the original location, therefore
     * overwriting the original file. 
     *
     * @access public
     * @param integer   $maxwidth   The maximum height of the resized image
     * @param integer   $maxheight  The maximum width of the resized image
     * @param integer   $quality    The quality of the image 
     */
    public function resize($maxwidth = 1000, 
                            $maxheight = 1000, 
                            $quality = 100)
    {
        $gdImage = getimagesize($this->getSourceFileLocation());
        
        $actualWidth = $gdImage[0];
        $actualHeight = $gdImage[1];
        
        // Do we even need to resize the image!?
        if ($actualWidth <= $maxwidth && $actualHeight <= $maxheight){
            return array('result' => self::RESULT_RESIZE_NOT_REQUIRED,
                            'newHeight' => $actualHeight,
                            'newWidth' => $actualWidth);
        }
        
        $ratio = $actualWidth / $maxwidth;
                
        // set the defaults:
        $newWidth = intval($actualWidth);
        $newHeight = intval($actualHeight);     
        
        if ($actualWidth > $maxwidth) {
            $newWidth = intval($maxwidth);
            $newHeight = intval($actualHeight / $ratio);
        }
        
        // once we've got the size width, is the height now small enough? 
        if ($newHeight > $maxheight) {
            // set a new ratio
            $ratio = $newHeight / $maxheight;
            $newWidth = intval($newWidth / $ratio);
            $newHeight = intval($maxheight);
        }       
    
        
        // Assign the class properties:
        $this->_maxwidth = $maxwidth;
        $this->_maxheight = $maxheight;
        $this->_quality = $quality;
        
        $this->_newWidth = $newWidth;
        $this->_newHeight = $newHeight;
        
        $this->_actualWidth = $actualWidth;
        $this->_actualHeight = $actualHeight;
        
        switch (strtolower($gdImage['mime'])) {
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
                throw new Exception('Mime Type \'' . $gdImage['mime'] . '\' not supported');
                break;
        }
        
        return array('result' => self::RESULT_RESIZE_SUCCESSFUL,
                        'newHeight' => $newHeight,
                        'newWidth' => $newWidth);
                
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
                            $this->_actualWidth, 
                            $this->_actualHeight);
                            
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
                            $this->_actualWidth, 
                            $this->_actualHeight);
                            
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
                            $this->_actualWidth, 
                            $this->_actualHeight);
                            
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
                            $this->_actualWidth, 
                            $this->_actualHeight);
                            
        $res = @imagegif($new_img, $this->getDestinationFileLocation());

        if (!$res){
            throw new Exception('imagegif failed. Check permission of file.');
        }
    }
}