<form method="POST" name="111" enctype="file">
    <input type="text" value="dfsdfhjkh">
    <input type="file">
    <input type="submit">
</form>
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
        background-color: lightcoral;
    }
    .identical{
        background-color: lightgreen;
    }
    th,td{
        border: 1px solid black;
    }
    table{
        border-collapse:  collapse;
    }
</style>

<?php
//echo '<pre>';
//echo '<pre>';
//die(var_dump($_POST));
$filename1 = 'uploads/file1.xtdd';
$fileContent1 = file_get_contents($filename1);
$fileContent1 = preg_replace('\'<av:\'', '<', $fileContent1);
$fileContent1 = preg_replace('\'</av:\'', '</', $fileContent1);
$xml1 = simplexml_load_string($fileContent1);
$json1 = json_encode($xml1);
$array1 = json_decode($json1, TRUE);
$filename2 = 'uploads/file2.xtdd';
$fileContent2 = file_get_contents($filename2);
$fileContent2 = preg_replace('\'<av:\'', '<', $fileContent2);
$fileContent2 = preg_replace('\'</av:\'', '</', $fileContent2);
$xml2 = simplexml_load_string($fileContent2);
$json2 = json_encode($xml2);
$array2 = json_decode($json2, TRUE);
echo '<pre>';





$result1 = [];
$result2 = [];
walkIntoArray($array1, $array2);

/**
 * Сравнивает два массива по ключам.
 * Выводит информацию о совпадениях, различиях и наличии элемента.
 */
function compareArrays() {
    
}

function walkIntoArray(&$array1, $array2) {
    foreach ($array1 as $key1 => &$value1) {
        if (is_array($value1)) {
            if (count($value1)) {

                walkIntoArray($value1, $array2[$key1]);
            }
        } else {
            $value2 = $array2[$key1];
//            echo $key1.'======'.$value1.'<br>';
//            echo $key1.'======'.$value2.'<br>';
//if(preg_match('/Подраздел2_ЦенБумРосЭмитент/',$key1)){
//}
            if ($value1 == $value2) {
                $value1 = array('key1' => $key1, 'value1' => $value1, 'key2' => $key1, 'value2' => $value2, 'identical' => 1);
//                die();
//                $array1['identical']=1;
//                $array2['identical'] = 1;
//                var_dump($value1);
//                echo '<span style="background-color: lightgreen;">' . $value1 . '</span>' . ' совпадает с ' . '<span style="background-color: lightgreen;">' . $value2 . '</span><br>';
            } else {
                $value1 = array('key1' => $key1, 'value1' => $value1, 'key2' => $key1, 'value2' => $value2, 'different' => 1);
//                $array1['different']=1;
//                $array2['different']=1;
//                
//                echo '<span style="background-color: lightcoral;">' . $value1 . '</span>' . ' отличается от ' . '<span style="background-color: lightcoral;">' . $value2 . '</span><br>';
            }
//                var_dump($value1);
        }
    }
}
    var_dump($array1);

echo "</pre>";
echo "<table>";
echo '<tr><th>ключ</th><th>значение из файла 1</th><th>значение из файла 2</th><tr>';
//printResult($array1);
echo "</table>";

function printResult($resultArray) {

    foreach ($resultArray as $keyRes => $valueRes) {
        if (is_array($valueRes)) {
//            var_dump("is_array($valueRes)");
            if (count($valueRes)) {
                if (!isset($valueRes['identical']) && !isset($valueRes['different'])) {
                    printResult($valueRes);
//                die(var_dump($valueRes));
                } else {
//                    die(var_dump($valueRes));
                    if (isset($valueRes['identical']) && $valueRes['identical'] == 1) {
                        echo '<tr class="identical"><td>' . $valueRes['key1'] . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
                    }
                    if (isset($valueRes['different']) && $valueRes['different'] == 1) {
//die(var_dump($valueRes));
                        if(is_array($valueRes['key1'])){
//                            var_dump($valueRes);
                        }
//                        echo $valueRes['key1'];
//                        echo '<br>';
//                        echo $valueRes['value1'];
//                        echo '<br>';
//                        echo $valueRes['value2'];
//                        echo '<br>';
//                        echo '<br>';
                        echo '<tr class="different"><td>' . $valueRes['key1'] . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
                    }
                }
            } else {
                var_dump($keyRes);
                echo '<tr><td>' . $keyRes . '</td><td>нет значения</td><td>нет значения</td><tr>';
            }
        }
    }
}

//    die(var_dump($resultArray));

//var_dump($array1['Раздел1Реквизиты']);
//var_dump($array2['Раздел1Реквизиты']);
//var_dump($array1);
//die(var_dump($array2));


    