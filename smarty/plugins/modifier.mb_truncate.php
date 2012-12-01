<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * File:     modifier.mb_truncate.php
 * Type:     modifier
 * Name:     mb_truncate
 * Purpose:  smartyでマルチバイトに対応したtruncate修飾子
 * Example:  {$msg|mb_truncate:8:'...'}
 * -------------------------------------------------------------
 */
function smarty_modifier_mb_truncate($string, $length = 80, $etc = '...')
{
    if (mb_strlen($string) > $length) {
        return mb_substr($string, 0, $length - mb_strlen($etc)).$etc;
    } else {
        return $string;
    }
}
?>
