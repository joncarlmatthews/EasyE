<?php

class EazyE_ImageCropper
{
    var $source_image;
    var $new_image_name;
    var $save_to_folder;
    
    const RESULT_CROP_ALREADY_SQUARE = 'crop_already_square';

    public function toSquare($location = 'center')
    {
        $info = GetImageSize($this->source_image);

        $width = $info[0];
        $height = $info[1];
        $mime = $info['mime'];
        
        $compression = 100;
        
        if($width == $height){
            
            // The source image is already a square.
            return array('result' => self::RESULT_CROP_ALREADY_SQUARE,
                            'new_file_path' => $this->source_image,
                            'height' => $height,
                            'width' => $width);
            
        }else{
        
            // What sort of image?
            $type = substr(strrchr($mime, '/'), 1);

            switch ($type){
                case 'jpeg':
                    $image_create_func = 'ImageCreateFromJPEG';
                    $image_save_func = 'ImageJPEG';
                    $new_image_ext = 'jpg';
                    break;

                case 'png':
                    $image_create_func = 'ImageCreateFromPNG';
                    $image_save_func = 'ImagePNG';
                    $new_image_ext = 'png';
                    $compression = 9;
                    break;

                case 'bmp':
                    $image_create_func = 'ImageCreateFromBMP';
                    $image_save_func = 'ImageBMP';
                    $new_image_ext = 'bmp';
                    break;

                case 'gif':
                    $image_create_func = 'ImageCreateFromGIF';
                    $image_save_func = 'ImageGIF';
                    $new_image_ext = 'gif';
                    break;

                case 'vnd.wap.wbmp':
                    $image_create_func = 'ImageCreateFromWBMP';
                    $image_save_func = 'ImageWBMP';
                    $new_image_ext = 'bmp';
                    break;

                case 'xbm':
                    $image_create_func = 'ImageCreateFromXBM';
                    $image_save_func = 'ImageXBM';
                    $new_image_ext = 'xbm';
                    break;

                default:
                    $image_create_func = 'ImageCreateFromJPEG';
                    $image_save_func = 'ImageJPEG';
                    $new_image_ext = 'jpg';
            }

            // Coordinates calculator

            // Horizontal Rectangle?
            if($width > $height){
               if($location == 'center')
               {
               $x_pos = ($width - $height) / 2;
               $x_pos = ceil($x_pos);

               $y_pos = 0;
               }
               else if($location == 'left')
               {
               $x_pos = 0;
               $y_pos = 0;
               }
               else if($location == 'right')
               {
               $x_pos = ($width - $height);
               $y_pos = 0;
               }

               $new_width = $height;
               $new_height = $height;
                
            // Vertical Rectangle?
            }else if($height > $width){
                if($location == 'center'){
                    $x_pos = 0;

                    $y_pos = ($height - $width) / 2;
                    $y_pos = ceil($y_pos);
                }else if($location == 'left'){
                    $x_pos = 0;
                    $y_pos = 0;
                }else if($location == 'right'){
                    $x_pos = 0;
                    $y_pos = ($height - $width);
                }

                $new_width = $width;
                $new_height = $width;
            }

            $image = $image_create_func($this->source_image);
            
            if (!$image){
                throw new Exception('Could not create image using ' . $image_create_func);
            }

            $new_image = ImageCreateTrueColor($new_width, $new_height);

            // Crop to Square using the given dimensions
            // ImageCopy($new_image, $image, 0, 0, $x_pos, $y_pos, $width, $height);
            imagecopyresampled($new_image, $image, 0, 0, $x_pos, $y_pos, $width, $height, $width, $height);

            if($this->save_to_folder){
                
                if($this->new_image_name){
                    $new_name = $this->new_image_name.'.'.$new_image_ext;
                }else{
                    $new_name = $this->new_image_name( basename($this->source_image) ).'_square_'.$location.'.'.$new_image_ext;
                }

                $save_path = $this->save_to_folder.$new_name;
                
            }else{
                /* Show the image (on the fly) without saving it to a folder */
                header("Content-Type: ".$mime);

                $image_save_func($new_image);

                $save_path = '';
            }

            // Save image 
            $process = $image_save_func($new_image, $save_path, $compression);
            
            chmod($save_path, 0755);
            
            if (!$process){
                throw new Exception('EightyEight_Image_Cropper -
                                        There was a problem in saving the new file.');
            }

            return array('result' => $process, 'new_file_path' => $save_path);
        }
    }

    public function new_image_name($filename)
    {
        $string = trim($filename);
        $string = strtolower($string);
        $string = trim(ereg_replace("[^ A-Za-z0-9_]", " ", $string));
        $string = ereg_replace("[ \t\n\r]+", "_", $string);

        $string = str_replace(" ", '_', $string);
        $string = ereg_replace("[ _]+", "_", $string);

        return $string;
    }
}