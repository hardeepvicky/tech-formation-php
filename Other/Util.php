<?php
class Util
{
    /**
    * Following function convert any type of object to array
    * it can convert xml, json object to array
    * 
    * @param object $obj
    * @return array
    */
   public static function objToArray($obj) 
   {
       $arr = array();
       if (gettype($obj) == "object") 
       {
           $arr = get_object_vars($obj);

           foreach ($arr as $k => $v) 
           {
               $arr[$k] = self::objToArray($v);
           }
       } 
       else if (gettype($obj) == "array") 
       {
           foreach ($obj as $k => $v) 
           {
               $arr[$k] = self::objToArray($v);
           }
       }
       else 
       {
           $arr = $obj;
       }

       return $arr;
   }
   
   /**
    * sort array on basis char len
    * @param array $arr
    * @return array
    */
   public static function sortArrayOnValueStringLength($arr)
   {
       $temp_list = array_flip($arr);    
       $arr = array_keys($temp_list);

       $n = count($arr);
       for($i = 0; $i < $n; $i++)
       {
           for($a = $i + 1; $a < $n; $a++)
           {
               if (strlen($arr[$a]) < strlen($arr[$i]))
               {
                   $temp = $arr[$i];
                   $arr[$i] = $arr[$a];
                   $arr[$a] = $temp;
               }
           }
       }

       $ret = array();

       foreach ($arr as $v)
       {
           if (isset($temp_list[$v]))
           {
               $ret[$temp_list[$v]] = $v;
           }
       }
       return $ret;
   }
   
    /**
    * get rondom string in given char string
    * @param int $length
    * @param String $valid_chars
    * @return string
    */
   function getRandomString($length, $valid_chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890") 
   {
       $random_string = "";
       $num_valid_chars = strlen($valid_chars);
       for ($i = 0; $i < $length; $i++) 
       {
           $random_pick = mt_rand(1, $num_valid_chars);
           $random_char = trim($valid_chars[$random_pick - 1]);

           if (!$random_char)
           {
               $i--;
           }
           else
           {
               $random_string .= $random_char;
           }
       }
       return $random_string;
   }
}