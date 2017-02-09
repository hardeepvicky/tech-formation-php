<?php
/**
 * file Uplaod Utility
 * 
 * 
 * @created    22/04/2015
 * @package    Badge
 * @copyright  Copyright (C) 2015
 * @license    Proprietary
 * @author     Hardeep
 */

namespace techformation\File;

require_once(dirname(__FILE__) . '/../Other/S3.php');
require_once('FileUtility.php');

class S3FileUtility extends FileUtility
{
    public static $s3, $s3_access_key, $s3_secret_key;
    
    public $bucket;

    /**
     * @param int $max_size
     * @param array $extensions
     * @param string $s3_bucket
     * @param string $s3_access_key
     * @param string $s3_secret_key
     */
    public function __construct($max_size, $extensions, $s3_bucket, $s3_access_key = "", $s3_secret_key = "")
    {
        parent::__construct($max_size, $extensions);
        
        $this->bucket = $s3_bucket;
        
        self::s3Instance($s3_access_key, $s3_secret_key);
    }
    
    /**
     * @param string $s3_access_key
     * @param string $s3_secret_key
     * @return boolean
     */
    public static function s3Instance($s3_access_key, $s3_secret_key)
    {
        if (!$s3_access_key || $s3_secret_key)
        {
            return false;
        }
        
        if (self::$s3 == null || self::$access_key != $s3_access_key || self::$secret_key != $s3_secret_key)
        {
            self::$access_key = $s3_access_key;
            self::$secret_key = $s3_secret_key;
            
            self::$s3 = new S3($s3_access_key, $s3_secret_key);
        }
        
        return true;
    }

    /**
     * upload a file to destination
     * @param array $file
     * @param string $dest_path
     * @return boolean
     */
    public function uploadFile($file, $dest_path, $filename = "", $access_level = 'public-read')
    {
        //validating file
        if (!$this->validateFile($file))
        {
            return false;
        }
        
        $this->path = $dest_path = self::addPathSlashs($dest_path);
        
        if ($filename)
        {
           $this->filename = $filename;
           $this->file = $this->filename . "." . $this->extension;
        }
        else
        {
           $this->file = self::getAutoincreamentFileName($this->filename, $this->extension, $dest_path);
        }

        try
        {
            $result = self::$s3->putObjectFile($file['tmp_name'], $this->bucket, $this->path . $this->file, $access_level);
        } 
        catch (S3Exception $e)
        {
            $result = false;
            $this->errors[] = $e->getMessage();
        }

        if (!$result)
        {
            $this->errors[] = "Failed to Upload";
        }

        return $result;
    }
    
    /**
    * return filename which which will be save 
    * @param string $filename
    * @param string $ext
    * @param string $dest_path
    * @return string
    */
   public static function getAutoincreamentFileName($filename, $ext, $dest_path, $sep = "_", $i = 0)
   {
       $temp_name =  $i > 0 ? $filename . $sep . $i : $filename;
       
       if (self::$s3->getObjectInfo($this->bucket, $dest_path . $temp_name . "." . $ext, false))
       {
           return self::getAutoincreamentFileName($filename, $ext, $dest_path, $sep, $i + 1);
       }
       else
       {
           return $temp_name . "." . $ext;
       }
   }
}