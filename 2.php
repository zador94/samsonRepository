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

function mySortForKey($a, $b)
{

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


/*---------------------------------------------------------------------------------*/


function importXml($filename)
{
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=test_samson', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        $xml = file_get_contents($filename); // Чтение содержимого XML-файла в строку.
        $products = new SimpleXMLElement($xml); // Создание объекта SimpleXMLElement из строки XML.

        try {
            // Устанавливаем режим обработки ошибок PDO на исключения
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            foreach ($products->Товар as $product) {
                $name = (string)$product->attributes()->{'Название'};
                $code = (string)$product->attributes()->{'Код'};

                // Проверяем, существует ли продукт с таким названием
                $stmt = $pdo->prepare("SELECT * FROM a_product WHERE name = ?");
                $stmt->execute([$name]);

                if (!$stmt->fetch()) {
                    $insertProductQuery = "INSERT INTO a_product (code, name) VALUES (?, ?)";
                    $stmt = $pdo->prepare($insertProductQuery);
                    if (!empty($code)) {
                        $stmt->execute([$code, $name]);
                    } else {
                        $stmt->execute([NULL, $name]);
                    }
                    $productId = $pdo->lastInsertId();

                    // Загружаем цены с типами
                    $insertPriceQuery = "INSERT INTO a_price (product_id, type, price) VALUES (?, ?, ?)";
                    $stmt = $pdo->prepare($insertPriceQuery);
                    foreach ($product->Цена as $price) {
                        $type = (string)$price['Тип'];
                        $priceValue = (float)$price;
                        $stmt->execute([$productId, $type, $priceValue]);
                    }

                    // Загружаем свойства
                    if (isset($product->Свойства)) {
                        $insertPropertyQuery = "INSERT INTO a_property (product_id, property, name) VALUES (?, ?, ?)";
                        $stmt = $pdo->prepare($insertPropertyQuery);
                        foreach ($product->Свойства as $properties) {
                            foreach ($properties as $property => $value) {
                                $stmt->execute([$productId, $property, $value]);
                            }
                        }
                    }

                    // Загружаем разделы, вложенность разделов и связь разделов с товаром
                    $insertCategoryQuery = "INSERT INTO a_category (category_id, name, parent_id) VALUES (NULL, ?, ?)";
                    $stmtInsertCategory = $pdo->prepare($insertCategoryQuery);
                    $insertProductCategoryQuery = "INSERT INTO a_product_category (product_id, category_id) VALUES (?, ?)";
                    $stmtInsertProductCategory = $pdo->prepare($insertProductCategoryQuery);

                    $parentId = 0;
                    foreach ($product->Разделы->Раздел as $category_name) {
                        $stmt = $pdo->prepare("SELECT * FROM a_category WHERE name = ?");
                        $stmt->execute([$category_name]);
                        $category = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($category) {
                            $parentId = $category['parent_id'];
                            $stmtInsertProductCategory->execute([$productId, $category['category_id']]);
                        } else {
                            $stmtInsertCategory->execute([$category_name, $parentId]);
                            $parentId = $pdo->lastInsertId();
                            $stmtInsertProductCategory->execute([$productId, $pdo->lastInsertId()]);
                        }
                    }
                } else {
                    echo "В файле $filename обнаружены дубли товаров. Загружен только уникальный товар.";
                }
            }
        } catch (PDOException $e) {
            echo "Ошибка загрузки в базу данных: " . $e->getMessage();
        }
    } catch (PDOException $e) {
        echo "Невозможно установить соединение с базой данных";
    }
}

/*importXml('C:\OpenServer\domains\1a\2.xml');*/

function exportXml($a, $b)
{
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=test_samson', 'root', 'root', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

        // Получаем все вложенные рубрики
        $stmt = $pdo->prepare("SELECT category_id FROM a_category WHERE parent_id = ? OR category_id = ?");
        $stmt->execute([$b, $b]);
        $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($categories)) {
            // Получаем все товары, которые относятся к любой из полученных рубрик
            $placeholders = implode(',', array_fill(0, count($categories), '?'));
            $stmt = $pdo->prepare("SELECT product_id FROM a_product_category WHERE category_id IN ($placeholders)");
            $stmt->execute($categories);
            $products = $stmt->fetchAll(PDO::FETCH_COLUMN);

            // Создаем XML-документ
            $dom = new DOMDocument('1.0');
            $xml = $dom->appendChild($dom->createElement('Товары'));

            foreach ($products as $productId) {
                // Получаем информацию о товаре
                $stmt = $pdo->prepare("SELECT * FROM a_product WHERE product_id = ?");
                $stmt->execute([$productId]);
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                // Добавляем информацию о товаре в XML-документ
                $productNode = $xml->appendChild($dom->createElement('Товар'));
                $productNode->appendChild($dom->createAttribute('Код'))->appendChild($dom->createTextNode($product['code']));
                $productNode->appendChild($dom->createAttribute('Название'))->appendChild($dom->createTextNode($product['name']));

                // Получаем цены товара
                $stmt = $pdo->prepare("SELECT * FROM a_price WHERE product_id = ?");
                $stmt->execute([$productId]);
                $prices = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Добавляем цены товара в XML-документ
                foreach ($prices as $price) {
                    $priceNode = $productNode->appendChild($dom->createElement('Цена', $price['price']));
                    $priceNode->appendChild($dom->createAttribute('Тип'))->appendChild($dom->createTextNode($price['type']));
                }

                // Получаем свойства товара
                $stmt = $pdo->prepare("SELECT * FROM a_property WHERE product_id = ?");
                $stmt->execute([$productId]);
                $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Добавляем свойства товара в XML-документ
                if (!empty($properties)) {
                    $propertiesNode = $productNode->appendChild($dom->createElement('Свойства'));
                    foreach ($properties as $property) {
                        $propertiesNode->appendChild($dom->createElement($property['property'], $property['name']));
                    }
                }

                // Получаем рубрики товара
                $stmt = $pdo->prepare("SELECT ac.name FROM a_product_category apc JOIN a_category ac ON apc.category_id = ac.category_id WHERE apc.product_id = ?");
                $stmt->execute([$productId]);
                $categories = $stmt->fetchAll(PDO::FETCH_COLUMN);

                // Добавляем рубрики товара в XML-документ
                $categoriesNode = $productNode->appendChild($dom->createElement('Разделы'));
                foreach ($categories as $category) {
                    $categoriesNode->appendChild($dom->createElement('Раздел', $category));
                }
            }

            // Сохраняем XML-документ в файл
            $dom->formatOutput = true;
            $xmlContent = $dom->saveXML();
            $xmlContent = html_entity_decode($xmlContent, ENT_XML1, 'UTF-8');
            file_put_contents($a, $xmlContent);
        } else {
            echo "Нет категорий, соответствующих заданному коду";
        }

    } catch (PDOException $e) {
        echo "Ошибка: " . $e->getMessage();
    }
}

/*exportXml('C:\OpenServer\domains\1a\export.xml', '1');*/
































