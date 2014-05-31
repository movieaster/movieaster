<?php

namespace Movieaster\MovieManagerBundle\Component;

class StringUtil
{
	
    public static function createNullSaveString($attribut, $stringMap)
    {
		if(array_key_exists($attribut, $stringMap)) {
			return "".$stringMap[$attribut];
		}
        return "";
    }
    	
    public static function createCommaSeparatedList($attribut, $stringList)
    {
		$commaSeparated = "";
		for($i=0;$i<count($stringList)-1;$i++) {
			$value = $stringList[$i][$attribut];
			if($commaSeparated != "") {
				$commaSeparated .= ", ";
			}
			$commaSeparated .= $value;
		}    
        return $commaSeparated;
    }
    
    public static function createCommaSeparatedListByJob($attribut, $stringList, $job)
    {
		$commaSeparated = "";
		for($i=0;$i<count($stringList)-1;$i++) {
			if($stringList[$i][$attribut]["job"] == $job) {
				$value = $stringList[$i][$attribut];
				if($commaSeparated != "") {
					$commaSeparated .= ", ";
				}
				$commaSeparated .= $value;
			}
		}    
        return $commaSeparated;
    }

    public static function createBase64Image($imgUrl)
    {
	    if($imgUrl == "") {
			return "";
	    }
        $content = file_get_contents($imgUrl); 
        if($content !== false) {
            return "data:image/" . substr($imgUrl, -3) . ";base64," . base64_encode($content);
        }
        return "";
    }
}
?>