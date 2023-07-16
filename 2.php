<?php
function convertString(string $a, string $b): string
{
    if (substr_count($a, $b) >= 2) {
        $pos = strpos($a, $b);

        $secondPos = strpos($a, $b, $pos + strlen($b));

        $invertedSubstring = strrev($b);
        $a = substr_replace($a, $invertedSubstring, $secondPos, strlen($b));
    }

    return $a;
}


$str = 'Lorem amet dolor sit amet, consectetur amet elit. Omnis, rem!';
$podstr = 'amet';
echo convertString($str, $podstr);

