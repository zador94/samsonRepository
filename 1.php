<?php

function findSimple(int $a, int $b): array
{
    $primes = [];

    if ($a > 0 && $b > 0 && $a < $b) {
        for ($i = $a; $i <= $b; $i++) {
            $isPrime = true;

            if ($i < 2) {
                $isPrime = false;
            }

            for ($j = 2; $j <= sqrt($i); $j++) {
                if ($i % $j === 0) {
                    $isPrime = false;
                    break;
                }
            }

            if ($isPrime) {
                $primes[] = $i;
            }
        }
    } else {
        throw new Exception('Введенные данные не соответствуют условию');
    }

    return $primes;
}
$number1 = 2;
$number2 = 9;

try {
    print_r(findSimple($number1, $number2)) ;
} catch (Exception $e) {
    echo 'Ошибка: ' . $e->getMessage();
}

/*----------------------------------------------------*/

function createTrapeze(array $a): array
{
    if (empty($a)) {
        throw new Exception('Вы передали пустой массив');
    }
    foreach (array_chunk($a, 3) as $item) {
        $trapeze[] = array(
            'a' => $item[0],
            'b' => $item[1],
            'c' => $item[2]
        );
    }
    return $trapeze;
}

$arr = [1, 2, 3, 4, 5, 6, 7, 8, 9];
try {
    $resultArrayTrapeze = createTrapeze($arr);
} catch (Exception $ex) {
   echo $ex->getMessage();
}


/*----------------------------------------------------*/
function squareTrapeze(array &$a): void
{
    for ($i = 0; $i < count($a); $i++) {
        $a[$i]['s'] = intval(($a[$i]['a'] + $a[$i]['b'] + $a[$i]['c']) / 2);
    }
}

squareTrapeze($resultArrayTrapeze);
var_dump($resultArrayTrapeze);

/*----------------------------------------------------*/

function getSizeForLimit(array &$a, int $b): array
{
    $result = [];
    foreach ($a as $trapeze) {
        if ($trapeze['s'] >= $b) {
            $result[] = $trapeze;
        }
    }

    if (empty($result)) {
        throw new Exception("Площадей больше числа {$b} нет");
    }

    return $result;
}

try {
    $resultGetSizeForLimit = getSizeForLimit($resultArrayTrapeze, 10);
    print_r($resultGetSizeForLimit);
} catch (Exception $exception) {
    echo $exception->getMessage();
}

/*----------------------------------------------------*/

function getMin(array $a): int
{
    $resultMinNumber = $a[0];
    for ($i = 1; $i < count($a); $i++) {
        if ($resultMinNumber > $a[$i]) {
            $resultMinNumber = $a[$i];
        }
    }
    return $resultMinNumber;
}

/*----------------------------------------------------*/

$point = [1, 2, 3, 6, 8, 9, 0, 8, 5, 6];
echo getMin($point);

function printTrapeze(array &$a): void
{
    echo '<table border="1" width="50%">';
    echo '<tr>';
    echo '<th>A</th>';
    echo '<th>B</th>';
    echo '<th>C</th>';
    echo '<th>S</th>';
    echo '</tr>';

    foreach ($a as $item) {
        $isOdd = $item['s'] % 2 != 0;
        echo '<tr';
        if ($isOdd) {
            echo ' style="background-color: yellow;"';
        }
        echo '>';
        echo "<td align='center'>" . $item['a'] . "</td>";
        echo "<td align='center'>" . $item['b'] . "</td>";
        echo "<td align='center'>" . $item['c'] . "</td>";
        echo "<td align='center'> " . $item['s'] . "</td>";
        echo '</tr>';
    }

    echo '</table>';

}

printTrapeze($resultArrayTrapeze);

/*----------------------------------------------------*/


abstract class BaseMath
{
    abstract public function getValue(): float|int;

    public function exp1($a, $b, $c): float|int
    {
        return $a * pow($b, $c);
    }

    public function exp2($a, $b, $c): float|int
    {
        return pow(($a / $c), $b);
    }
}

class F1 extends BaseMath
{

    public function __construct(protected int $a, protected int $b, protected int $c)
    {
    }

    public function getA(): int
    {
        return $this->a;
    }

    public function setA(int $a): void
    {
        $this->a = $a;
    }

    public function getB(): int
    {
        return $this->b;
    }

    public function setB(int $b): void
    {
        $this->b = $b;
    }

    public function getC(): int
    {
        return $this->c;
    }


    public function setC(int $c): void
    {
        $this->c = $c;
    }

    public function getValue(): float|int
    {
        $f = $this->exp1($this->a, $this->b, $this->c) + pow(fmod($this->exp2($this->a, $this->b, $this->c), 3), min($this->a, $this->b, $this->c));
        return $f;
    }
}


$f1 = new F1(2, 3, 4);
$result = $f1->getValue();
echo $result;




