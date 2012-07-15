<?php
require_once( $_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR.'config.php' );
ini_set("memory_limit","40M"); //aumenta temporaneamente la memoria dedicata a PHP

/**
 * Handle file uploads via XMLHttpRequest
 */
class qqUploadedFileXhr 
{    
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) 
    {    
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $realSize = stream_copy_to_stream($input, $temp);
        fclose($input);
        
        if ($realSize != $this->getSize())
        {            
            return false;
        }
        
        $target = fopen($path, "w");        
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
        
        return true;
    }
    
    function getName() 
    {
        return $_GET['qqfile'];
    }
    
    function getSize() 
    {
        if (isset($_SERVER["CONTENT_LENGTH"]))
        {
            return (int)$_SERVER["CONTENT_LENGTH"];            
        }
        else 
        {
            throw new Exception('Getting content length is not supported.');
        }      
    }   
}

/**
 * Handle file uploads via regular form post (uses the $_FILES array)
 */
class qqUploadedFileForm 
{  
    /**
     * Save the file to the specified path
     * @return boolean TRUE on success
     */
    function save($path) 
    {
        if(!move_uploaded_file($_FILES['qqfile']['tmp_name'], $path))
        {
            return false;
        }
        return true;
    }
    
    function getName() 
    {
        return $_FILES['qqfile']['name'];
    }
    function getSize() 
    {
        return $_FILES['qqfile']['size'];
    }
}

class qqFileUploader 
{
    private $allowedExtensions = array();
    private $sizeLimit = 10485760;
    private $file;
    public $thumbWidth = 200;
    public $thumbName = null;

    function __construct(array $allowedExtensions = array(), $sizeLimit = 10485760)
    {        
        $allowedExtensions = array_map("strtolower", $allowedExtensions);
            
        $this->allowedExtensions = $allowedExtensions;        
        $this->sizeLimit = $sizeLimit;
        
        $this->checkServerSettings();       

        if (isset($_GET['qqfile'])) 
        {
            $this->file = new qqUploadedFileXhr();
        }
        elseif (isset($_FILES['qqfile'])) 
        {
            $this->file = new qqUploadedFileForm();
        }
        else
        {
            $this->file = false; 
        }
    }
    
    private function checkServerSettings()
    {        
        $postSize = $this->toBytes(ini_get('post_max_size'));
        $uploadSize = $this->toBytes(ini_get('upload_max_filesize'));        
        
        if ($postSize < $this->sizeLimit || $uploadSize < $this->sizeLimit)
        {
            $size = max(1, $this->sizeLimit / 1024 / 1024) . 'M';             
            die("{'success':false, error':'increase post_max_size and upload_max_filesize to $size'}");    
        }        
    }
    
    private function toBytes($str)
    {
        $val = trim($str);
        $last = strtolower($str[strlen($str)-1]);
        
        switch($last) 
        {
            case 'g': $val *= 1024;
            case 'm': $val *= 1024;
            case 'k': $val *= 1024;        
        }
        return $val;
    }
    
    /**
     * Returns array('success'=>true) or array('error'=>'error message')
     */
    function handleUpload($uploadDirectory, $manageThumb = FALSE, $replaceOldFile = FALSE)
    {
        if (!is_writable($uploadDirectory))
        {
            return array('success'=>false, 'error' => "Server error. Upload directory ".$uploadDirectory." isn't writable.");
        }
        
        if (!$this->file)
        {
            return array('success'=>false, 'error' => 'No files were uploaded.');
        }
        
        $size = $this->file->getSize();
        
        if ($size == 0) 
        {
            return array('success'=>false, 'error' => 'File is empty');
        }
        
        if ($size > $this->sizeLimit) 
        {
            return array('success'=>false, 'error' => 'File is too large');
        }
        
        $pathinfo = pathinfo($this->file->getName());
        $filename = $pathinfo['filename'];
        //$filename = md5(uniqid());
        $ext = $pathinfo['extension'];

        if($this->allowedExtensions && !in_array(strtolower($ext), $this->allowedExtensions))
        {
            $these = implode(', ', $this->allowedExtensions);
            return array('success'=>false, 'error' => 'File has an invalid extension, it should be one of '. $these . '.');
        }
        
        if(!$replaceOldFile)
        {
            /// don't overwrite previous files that were uploaded
            while (file_exists($uploadDirectory . $filename . '.' . $ext)) 
            {
                $filename .= rand(10, 99);
            }
        }
        
        if ($this->file->save($uploadDirectory . $filename . '.' . $ext))
        {
        	$returnArray = array('success'=>true, 'file_url'=> WEB_ROOT."/".$uploadDirectory.$filename . '.' . $ext, 'filename'=>$filename . '.' . $ext);
        	
     		// Success
     		if(!$manageThumb)
     		{
            	return $returnArray;
     		}
     		else 
     		{
   				if($this->thumbnail($uploadDirectory.$filename.'.'.$ext, $this->thumbWidth, $uploadDirectory, $filename))
   				{
   					$returnArray['thumb'] = $filename."thumb.png";
   					$returnArray['thumb_url'] = WEB_ROOT."/".$uploadDirectory.$filename.'thumb.png';
   				}
   				else 
   				{
   					$returnArray['warning'] = "Error creating thumb";
   				}
   				
   				return $returnArray;
     		}
        } 
        else 
        {
            return array('success'=>false, 'error'=> 'Could not save uploaded file.' .
                'The upload was cancelled, or server error encountered');
        }
    }    

    function thumbnail($file_path, $new_width, $uploadDirectory, $filename)
    {
    	$info_image = getimagesize($file_path);
    	$height = $info_image[1];
    	$width = $info_image[0];
    	$area = $height/$width;
    	
    	$this->thumbName = $filename."thumb.png";

    	// calcolo in proprozione la width
    	$new_height = (int) ($area * $new_width);

    	// resample
    	$new_image = imagecreatetruecolor($new_width, $new_height); #creo la nuova immagine vuota
    	
    	$image = null;

    	switch ($info_image['mime'])
    	{
    		case "image/jpeg":
    			$image = imagecreatefromjpeg($file_path);
    			break;
    				
    		case "image/gif":
    			$image = imagecreatefromgif($file_path);
    			break;
    				
    		case "image/png":
    			$image = imagecreatefrompng($file_path);
    			break;
    	}
    	
    	imagecopyresized($new_image, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
    	
    	return imagepng($new_image, $uploadDirectory.$this->thumbName);
    }
}