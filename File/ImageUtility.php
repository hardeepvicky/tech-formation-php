<?php
/**
 * @created    23-01-2017
 * @copyright  Copyright (C) 2017
 * @author     Hardeep
 * @version    1.0
 */

namespace techformation\File;

class ImageUtility
{
    public static function createImageFromBase64($file, $base_string)
    {
        $data = explode(',', $base_string);
        
        $data = count($data) > 1 ? $data[1] : $data[0];

        file_put_contents($file, base64_decode($data));

        return true; 
    }
    
    /**
     * change the image extenstion depending upon image type
     * @Created 17-11-2016
     * @Modified 17-11-2016
     * @param string $image 
     */
    function getActualImageName($image)
    {
        chmod($image, 0777);

        //get extenstion
        $ext = strtolower(pathinfo($image, PATHINFO_EXTENSION));

        //get image type
        $image_type = exif_imagetype($image);

        if ($image_type == IMAGETYPE_JPEG && ($ext != "jpg" || $ext != "jpeg"))
        {
            $ext = "jpg";
        }

        if ($image_type == IMAGETYPE_PNG && $ext != "png")
        {
            $ext = "png";
        }

        if ($image_type == IMAGETYPE_GIF && $ext != "gif")
        {
            $ext = "gif";
        }

        if ($image_type == IMAGETYPE_BMP && $ext != "bmp")
        {
            $ext = "bmp";
        }

        if ($image_type == IMAGETYPE_SWF && $ext != "swf")
        {
            $ext = "swf";
        }

        $name = pathinfo($image, PATHINFO_FILENAME);

        return  $name . "." . $ext;
    }

    /**
     * Resize the image 
     * @Created 17-11-2016
     * @Modified 17-11-2016
     * @param String $source_image
     * @param int $width
     * @param int $height
     * @param String $dest_image
     * @return image_resource
     */
    function resizeImage($source_image, $width = null, $height = null, $dest_image = null)
    {
        chmod($source_image, 0777);

        $image_type = exif_imagetype($source_image);

        if (!$image_type)
        {
            return false;
        }

        list($w, $h) = getimagesize($source_image);

        $toWidth = $width ? $width : $w;
        $toHeight = $height ? $height : $h;

        $xscale = $w / $toWidth;
        $yscale = $h / $toHeight;

        if ($yscale > $xscale)
        {
            $toWidth = round($w * (1 / $yscale));
            $toHeight = round($h * (1 / $yscale));
        } 
        else
        {
            $toWidth = round($w * (1 / $xscale));
            $toHeight = round($h * (1 / $xscale));
        }

        //getting image resource depending upon its type
        $src_r = null;
        switch($image_type)
        {
            case IMAGETYPE_PNG:    
                $src_r = imagecreatefrompng($source_image);
                imagealphablending($src_r, true);
                imagesavealpha($src_r, true);
                break;

            case IMAGETYPE_GIF:
                $src_r = imagecreatefromgif($source_image);
                break;

            case IMAGETYPE_JPEG:
                $src_r = imagecreatefromjpeg($source_image);
                break;
        }

        if (!$src_r)
        {
            return false;
        }

        //creating dest resource
        $dest_r = imagecreatetruecolor($toWidth, $toHeight);

        imagecopyresampled($dest_r, $src_r, 0, 0, 0, 0, $toWidth, $toHeight, $w, $h);

        if (!empty($dest_image))
        {
            if (file_exists($dest_image))
            {
                chmod($dest_image, 0777);
            }

            //saving image to dest_image
            switch($image_type)
            {
                case IMAGETYPE_PNG:    
                    imagepng($dest_r, $dest_image);
                    break;

                case IMAGETYPE_GIF:
                    imagegif($dest_r, $dest_image);
                    break;

                case IMAGETYPE_JPEG:
                    imagejpeg($dest_r, $dest_image, 100);
                    break;

                default :
                    return false;
            }

            return true;
        } 

        return $dest_r;
    }


    /**
     * Crop the image
     * @param string $source_image Image with path
     * @param int $x where crop start horizontally
     * @param int $y where crop start verticcally
     * @param int $width  
     * @param int $height
     * @param string $dest_image
     * @return image_resource
     */
    function cropImage($source_image, $x, $y, $width, $height, $dest_image = null)
    {
        chmod($source_image, 0777);

        $image_type = exif_imagetype($source_image);

        if (!$image_type)
        {
             return false;
        }

        //getting image into variable depending upon its type
        $src_r = null;
        switch($image_type)
        {
            case IMAGETYPE_PNG:    
                $src_r = imagecreatefrompng($source_image);
                imagealphablending($src_r, true);
                imagesavealpha($src_r, true);
                break;

            case IMAGETYPE_GIF:
                $src_r = imagecreatefromgif($source_image);
                break;

            case IMAGETYPE_JPEG:
                $src_r = imagecreatefromjpeg($source_image);
                break;
        }

        if (!$src_r)
        {
            return false;
        }

        //create dest image resource
        $dst_r = imagecreatetruecolor($width, $height);

        imagecopy($dst_r, $src_r, 0, 0, $x, $y, $width, $height);

        if (!empty($dest_image))
        {
            if (file_exists($dest_image))
            {
                chmod($dest_image, 0777);
            }

            //saving image to dest_image
            switch($image_type)
            {
                case IMAGETYPE_PNG:    
                    imagepng($dst_r, $dest_image);
                    break;

                case IMAGETYPE_GIF:
                    imagegif($dst_r, $dest_image);
                    break;

                case IMAGETYPE_JPEG:
                    imagejpeg($dst_r, $dest_image, 100);
                    break;

                default :
                    return false;
            }

            return true;
        }

        return $dst_r;
    }

    /**
     * Rotate JPG Image to correct direction
     * @param string $src_image
     * @param string $dest_image
     * @return boolean
     */
    function rotateImage($src_image, $dest_image = null)
    {
        chmod($src_image, 0777);

        $image_type = exif_imagetype($src_image);

        if ($image_type != IMAGETYPE_JPEG)
        {
            return false;
        }

        $exif = @exif_read_data($src_image);

        if (!empty($exif['Orientation'])) 
        {
            $src_r = imagecreatefromjpeg($src_image);

            switch ($exif['Orientation']) 
            {
                case 3:
                    $src_r = imagerotate($src_r, 180, 0);
                    break;

                case 6:
                    $src_r = imagerotate($src_r, -90, 0);
                    break;

                case 8:
                    $src_r = imagerotate($src_r, 90, 0);
                    break;
            }

            if ($dest_image)
            {
                if (file_exists($dest_image))
                {
                    chmod($dest_image, 0777);
                }

                imagejpeg($src_r, $dest_image);
            }
            else
            {
                return $src_r;
            }
        }

        return true;
    }
}