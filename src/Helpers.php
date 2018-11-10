<?php
/**
 * Created by PhpStorm.
 * User: mikael
 * Date: 11/06/2018
 * Time: 16:02
 */
namespace leadingfellows;

class Helpers {
    public static function startsWith( $haystack, $needle ){
        return $needle === ''.substr( $haystack, 0, strlen( $needle )); // substr's false => empty string
    }


    public static function endsWith($haystack, $needle) {
        // search forward starting from end minus needle length characters
        return $needle === "" || (($temp = strlen($haystack) - strlen($needle)) >= 0 && strpos($haystack, $needle, $temp) !== false);
    }

    public static function strlistToArray($strlist) {
        $output = array();
        $sp = explode(",", $strlist);
        foreach($sp as $potential_item) {
            $potential_item = trim($potential_item);
            if (!$potential_item || strlen($potential_item) < 1) continue;

            $output[$potential_item] = $potential_item;
        }
        return array_keys($output);
    }

}
