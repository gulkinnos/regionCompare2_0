<style>
    .commonDiv div{
        display: inline-block;
        width: 50%;
        float: left;
    }
    .div1{
        background-color: thistle;
    }
    .div2{

        background-color: bisque;
    }
    .different{
        background-color: #ffbfbf;
    }
    .superDifferent{
        background-color: #ffbfbf;
    }
    .identical{
        background-color: #c6fdc6;
    }
    .impossible{
        background-color: orange;
    }
    .ISINheader td{
        text-align: left;
        padding-top: 5px;
        padding-left: 50px;
        font-weight: bold;
    }
    th,td{
        border: 1px solid black;
    }
    table{
        border-collapse:  collapse;
    }
    table > td {
        width: 30%;
    }
    #version{
        background-color: lightgray;
        width: 30%;
        position: absolute;
        top: 0;
        right: 0;
        font-size: 0.7rem;
    }
</style>
<div id="version">v.1.5 13.09.2016
    <br>05.09.2016 Пофиксил ошибку группировки по ISIN
    <br>11.09.2016 Пофиксил ошибку группировки по стоимости векселей
    <br>11.09.2016 Немного доработана вёрстка. Теперь ячейки в строке не объединяются.
    <br>13.09.2016 Пофиксил ошибку группировки по ISIN. Если уже сгруппированно, то не группируется по сумме.
</div>
<form method="POST" name="111" enctype="multipart/form-data">
    <label for="file1input">файл с данными от УК
        <input id="file1input" type="file" name="file1" placeholder="файл с данными от УК"></label><hr>
    <label for="file2input">файл с данными СПЕЦДЕПА
        <input id="file2input" type="file" name="file2" placeholder="файл с данными СПЕЦДЕПА"></label><hr>

    <button id="submitInput" type="submit">Запустить проверку</button>
    <br>
    <a href="/manageVocab.php" target="_blank">Словарь соответствий</a>
    <br>
</form>
<?php
//die(phpinfo());
set_time_limit(3600);
//echo '<pre>';
//die('отладка');
$filename1 = '';

if (isset($_FILES['file1']['tmp_name'])) {
    $filename1 = $_FILES['file1']['tmp_name'];
}
$filename2 = '';

if (isset($_FILES['file2']['tmp_name'])) {
    $filename2 = $_FILES['file2']['tmp_name'];
}
if (!$filename1 == '') {
    if (file_exists($filename1)) {

        $fileContent1 = file_get_contents($filename1);
    } else {
        die('Файл 1 выбран, но не загрузился на сервер. Проверьте права или что-то ещё на серваке');
    }
} else {
    die('Не выбран файл 1');
}
if (!$filename2 == '') {
    if (file_exists($filename2)) {

        $fileContent2 = file_get_contents($filename2);
    } else {
        die('Файл 2 выбран, но не загрузился на сервер. Проверьте права или что-то ещё на серваке');
    }
} else {
    die('Не выбран файл 2');
}
$fileContent1 = preg_replace('/<av:ОКУД0420502[^>]+>+/', '<av:ОКУД0420502>', $fileContent1);
$fileContent1 = preg_replace('/<([\\/])av:ОКУД0420502[^>]?>+/', '</av:ОКУД0420502>', $fileContent1);
$fileContent1 = preg_replace('\'<av:\'', '<', $fileContent1);
$fileContent1 = preg_replace('\'</av:\'', '</', $fileContent1);
$xml1 = simplexml_load_string($fileContent1);
$fileContent2 = preg_replace('/<av:ОКУД0420502[^>]+>+/', '<av:ОКУД0420502>', $fileContent2);
$fileContent2 = preg_replace('/<([\\/])av:ОКУД0420502[^>]?>+/', '</av:ОКУД0420502>', $fileContent2);
$fileContent2 = preg_replace('\'<av:\'', '<', $fileContent2);
$fileContent2 = preg_replace('\'</av:\'', '</', $fileContent2);
$xml2 = simplexml_load_string($fileContent2);
//foreach ($xml1 as $key => $value) {
////    var_dump($value);
//}

$result = new result();
//$result->getNodesValues($xml1, 1);
//$result->getNodesValues($xml2, 2);
$result->getScalarValue($xml1, 1);
$result->getScalarValue($xml2, 2);
//ksort($result->result);
//echo '<pre>';
//die(print_r($result));
//die(var_dump($result));
printResultTable($result->result);
die();
echo '<pre>';
die(print_r($result->result));

//die(var_dump($xml1 == $xml2));

class result {

    public $result = [];

    function getNodesValues(\SimpleXMLElement $xmlNode, $objectID) {

        $nodeName = $xmlNode->getName();


        die(var_dump($nodeName));
    }

    function getScalarValue(\SimpleXMLElement $xmlNode, $objectID, $parentKey = 'root', $index = 0) {
        $nodeName = $xmlNode->getName();
        if (isset($this->result[$parentKey][$nodeName]['value' . $objectID])) {
//            echo 'ПОВТОР';
//            var_dump($nodeName);
//            echo '<hr>';
            $index++;
            $nodeName .= '__' . $index;
        } else {
            $index = 0;
        }



        $this->result[$parentKey][$nodeName]['parent'] = $parentKey;
        $this->result[$parentKey][$nodeName]['nodeName'] = $nodeName;
        $this->result[$parentKey][$nodeName]['value' . $objectID] = trim(strip_tags((string) $xmlNode));
        if ($xmlNode->children()) {
            $this->result[$parentKey][$nodeName]['hasChildren' . $objectID] = count($xmlNode->children());
            foreach ($xmlNode->children() as $childrenKey => $childrenNode) {
                $this->getScalarValue($childrenNode, $objectID, $nodeName, $index);
            }
        } else {
            $this->result[$parentKey][$nodeName]['hasChildren' . $objectID] = 0;
        }
    }

//    function getScalarValue(\SimpleXMLElement $xmlNode, $objectID, $parentKey = '', $index = 0) {
//        $nodeName = $xmlNode->getName();
//        $xpath=$parentKey.'/'.$nodeName;
//        
//        if ($xmlNode->children()) {
//            foreach ($xmlNode->children() as $childrenKey => $childrenNode) {
//                $this->getScalarValue($childrenNode, $objectID, $xpath, $index);
//            }
//        } else {
//            $this->result[$xpath]['nodeName'] = $nodeName;
//            $this->result[$xpath]['parentXpath'] = $parentKey;
//            $this->result[$xpath]['xpath'] = $xpath;
//            $this->result[$xpath]['value'.$objectID] = trim(strip_tags((string) $xmlNode));
//        }
//    }
}

function printResultTable($compareResult) {
    $result = 'Сравнение не удалось';
    if (is_array($compareResult) && count($compareResult)) {
        echo '<table>';
        echo('<tr>
            <td>Название строки</td>' .
//            '<td>родительский</td>'.
        '<td>isISIN</td>
            <td>значение из файла 1</td>
            <td>значение из файла 2</td>
            </tr>');

        foreach ($compareResult as $nodeName => $values) {
//            die(var_dump($values));
//           if(!isset($values[2])){
//               var_dump($nodeName,$values);
//           }
            if (!isset($values['value1'])) {
                $value1 = 'ЗНАЧЕНИЕ ОТСУТСТВУЕТ';
            } else {

                $value1 = $values['value1'];
            }

            if (!isset($values['value2'])) {
                $value2 = 'ЗНАЧЕНИЕ ОТСУТСТВУЕТ';
            } else {

                $value2 = $values['value2'];
            }
            if (!isset($values['nodeName'])) {
                $nodeName = 'нет имени';
            } else {

                $nodeName = $values['nodeName'];
            }



            echo('<tr>
                    <td>' . $nodeName . '</td>' .
//                    '<td>' . $values['parentXpath'] . '</td>'.
            '<td>' . $values['isISIN'] . '</td>
                    <td>' . $value1 . '</td>
                    <td>' . $value2 . '</td>
                 </tr>');
        }
        echo '</table>';
    }
}
