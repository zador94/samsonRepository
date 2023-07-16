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

/*----------------------------------------------------------------------------------------*/

function mySortForKey($a, $b) {

    for ($i = 0; $i < count($a); $i++) {
        if (!isset($a[$i][$b])) {
            throw new Exception("Ошибка: отсутствует ключ {$b} в одном из вложенных массивов");
        }
    }

    for ($i = 0; $i < count($a) - 1; $i++) {
        for ($j = 0; $j < count($a) - $i - 1; $j++) {
            if ($a[$j][$b] > $a[$j + 1][$b]) {
                $temp = $a[$j];
                $a[$j] = $a[$j + 1];
                $a[$j + 1] = $temp;
            }
        }
    }

    return $a;
}

$a = [
    ['a' => 2, 'b' => 1],
    ['a' => 1, 'b' => 3]
];
$b = 'a';

try {
    $result = mySortForKey($a, $b);
    var_dump($result);
} catch (Exception $e) {
    echo $e->getMessage();
}
