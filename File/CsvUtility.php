<?php
/**
 * @created    22/04/2015
 * @package    Badge
 * @copyright  Copyright (C) 2015
 * @license    Proprietary
 * @author     Hardeep
 */
class CsvUtility
{
    public static function fetchCSV($filename, $header = true, $delimiter = ',') 
    {
        if (!file_exists($filename) || !is_readable($filename)) 
        {
            return FALSE;
        }
        $data = array();
        $header_data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) 
        {
            $r = 0;
            while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) 
            {
                foreach($row as $k  => $v)
                {
                    $row[$k] = utf8_encode($v);
                }
                
                if ($header && $r == 0) 
                {
                    $header_data = $row;               
                } 
                else 
                {
                    if ($header)
                    {
						$insert = false;
						
						foreach($row as $key => $val)
						{
							if (trim($val))
							{
								$insert = true;
							}
						}
						
						if ($insert)
						{
							for($i = 0; $i < count ($row); $i++)
							{	
								$data[$r][$header_data[$i]] = $row[$i];
							}
						}
                    }
                    else
                    {
                        $data[$r] = $row;
                    }
                }
                $r++;
            }
            fclose($handle);
        }
        return $data;
    }

    public static function writeCSV($file, $data, $key_as_header = false, $delimeter = ',', $mode = "w") 
    {
        $handle = fopen($file, $mode);
        if ($handle) 
        {
            if ($key_as_header)
            {
                $i = 0;
                foreach ($data as $key => $line) 
                {
                    if ($i == 0)
                    {
                        fputcsv($handle, array_keys($line), $delimeter);
                    }
                    
                    fputcsv($handle, $line, $delimeter);
                    
                    $i++;
                }
            }
            else
            {
                if (!empty($data['header']) && isset($data['header']) && $mode != "a") 
                {
                    fputcsv($handle, $data['header'], $delimeter);
                }

                if (!empty($data['data']) && isset($data['data'])) 
                {
                    $data = $data['data'];
                }
                
                foreach ($data as $key => $line) 
                {
                    fputcsv($handle, $line, $delimeter);
                }
            }
            
            fclose($handle);
            
            return true;
        }
        else
        {
            return false; 
        }
    }
}