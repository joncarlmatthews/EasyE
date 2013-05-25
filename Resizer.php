<?php

/**
 * EazyE ImageResizer
 *
 * @link      https://github.com/joncarlmatthews/ImageResizer/ for the canonical source repository
 * @license   GNU General Public License
 *
 * File format: UNIX
 * File encoding: UTF8
 * File indentation: Spaces (4). No tabs
 */

/**
 * The EazyE_ImageResizer class provides methods for resizing an image.
 * 
 * @author Jon Matthews <joncarlmatthews@gmail.com> 
 */
class EazyE_ImageResizer
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
     * The file path of the image to resize.
     * 
     * @access private
     * @var NULL|string
     */
    private static $_filePath = null;

    /**
     * The location of where to save the resized image to.
     * 
     * @access private
     * @var NULL|string
     */
    private static $_newPath = null;

    /**
     * The maximum width that the image should be.
     * 
     * @access private
     * @var NULL|string
     */
    private static $_maxwidth = null;

    /**
     * The maximum height that the image should be.
     * 
     * @access private
     * @var NULL|string
     */
    private static $_maxheight = null;

    /**
     * The quality (0 - 100) that the resized image should be saved at.
     * 
     * @access private
     * @var integer
     */
    private static $_quality = 0;
    
    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private static $_newWidth = null;

    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private static $_newHeight = null;
    
    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private static $_actualWidth = null;

    /**
     * BLA
     * 
     * @access private
     * @var NULL|string
     */
    private static $_actualHeight = null;
    
    /**
     * Takes an image (from a file path) and resizes it. The newly resized image
     * can then either be created in a different location, therefore maintainig 
     * the original file. Or can be created in the original location, therefore
     * overwriting the original file. 
     *
     * @static 
     * @access public
     * @param string    $filePath   The file path of the image to resize
     * @param string    $newPath    The file path where the resized image will 
     *                              be created. Null to overwrite original.
     * @param integer   $maxwidth   The maximum height of the resized image
     * @param integer   $maxheight  The maximum width of the resized image
     * @param integer   $quality    The quality of the image 
     */
    public static function resizeExistingImage($filePath, 
                                                $newPath = null, 
                                                $maxwidth = 1000, 
                                                $maxheight = 1000, 
                                                $quality = 100)
    {
        if (is_null($newPath)) {
            $newPath = $filePath;
        }
        
        $gdImage = getimagesize($filePath);
        
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
        self::$_filePath = $filePath;
        self::$_newPath = $newPath;
        self::$_maxwidth = $maxwidth;
        self::$_maxheight = $maxheight;
        self::$_quality = $quality;
        
        self::$_newWidth = $newWidth;
        self::$_newHeight = $newHeight;
        
        self::$_actualWidth = $actualWidth;
        self::$_actualHeight = $actualHeight;
        
        switch (strtolower($gdImage['mime'])) {
            case 'image/jpeg':
                self::_createFromJpeg();
                break;
            case 'image/pjpeg':
                self::_createFromPJpeg();
                break;
            case 'image/png':
                self::_createFromPng();
                break;
            case 'image/gif':
                self::_createFromGif();
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
     * @static
     * @access public
     * @author Jon Matthews <joncarlmatthews@gmail.com>
     * @return void
     */
    private static function _createFromJpeg()
    {
        $img = imagecreatefromjpeg(self::$_filePath);
        
        $new_img = imagecreatetruecolor(self::$_newWidth, self::$_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            self::$_newWidth, 
                            self::$_newHeight, 
                            self::$_actualWidth, 
                            self::$_actualHeight);
                            
        imagejpeg($new_img, self::$_newPath, self::$_quality);
    }
    
    /**
     * Resizes images of type image/jpeg.
     *
     * @static 
     * @access private
     * @return void
     */
    private static function _createFromPJpeg()
    {
        $img = imagecreatefromjpeg(self::$_filePath);
        
        imageinterlace($img, 1);
        
        $new_img = imagecreatetruecolor(self::$_newWidth, self::$_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            self::$_newWidth, 
                            self::$_newHeight, 
                            self::$_actualWidth, 
                            self::$_actualHeight);
                            
        imagejpeg($new_img, self::$_newPath, self::$_quality);
    }
    
    /**
     * Resizes images of type image/png.
     *
     * @static 
     * @access private
     * @return void
     */
    private static function _createFromPng()
    {
        $img = imagecreatefrompng(self::$_filePath);
        
        $new_img = imagecreatetruecolor(self::$_newWidth, self::$_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            self::$_newWidth, 
                            self::$_newHeight, 
                            self::$_actualWidth, 
                            self::$_actualHeight);
                            
        imagepng($new_img, self::$_newPath);        
    }
    
    /**
     * Resizes images of type image/gif.
     *
     * @static 
     * @access private
     * @return void
     */
    private static function _createFromGif()
    {
        $img = imagecreatefromgif(self::$_filePath);
        
        $new_img = imagecreatetruecolor(self::$_newWidth, self::$_newHeight);
        
        imagecopyresampled($new_img, 
                            $img, 
                            0, 
                            0, 
                            0, 
                            0, 
                            self::$_newWidth, 
                            self::$_newHeight, 
                            self::$_actualWidth, 
                            self::$_actualHeight);
                            
        imagegif($new_img, self::$_newPath);    
    }


}