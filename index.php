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

function getExternalVocab() {
    $result = [];
    $currentVocabFileNames = glob('./vocab/*.csv');
    if (is_array($currentVocabFileNames) && count($currentVocabFileNames)) {
        $currentVocabFileName = array_pop($currentVocabFileNames);
        $result = csv_to_array($currentVocabFileName, '~');
    }
    return $result;
}

function csv_to_array($filename = '', $delimiter = ',') {
    if (!file_exists($filename) || !is_readable($filename))
        return FALSE;

    $header = NULL;
    $data = array();
    if (($handle = fopen($filename, 'r')) !== FALSE) {
        while (($row = fgetcsv($handle, 0, $delimiter)) !== FALSE) {
            if (!$header) {
                $header = $row;
            } else {
                $data[] = $row;
            }
        }
        fclose($handle);
    }
    return $data;
}

$fileContent1 = preg_replace('/<av:ОКУД0420502[^>]+>+/', '<av:ОКУД0420502>', $fileContent1);
$fileContent1 = preg_replace('/<([\\/])av:ОКУД0420502[^>]?>+/', '</av:ОКУД0420502>', $fileContent1);
$fileContent1 = preg_replace('\'<av:\'', '<', $fileContent1);
$fileContent1 = preg_replace('\'</av:\'', '</', $fileContent1);
die(var_dump($fileContent1));
$xml1 = simplexml_load_string($fileContent1);
$json1 = json_encode($xml1);
$array1 = json_decode($json1, TRUE);
$fileContent2 = preg_replace('/<av:ОКУД0420502[^>]+>+/', '<av:ОКУД0420502>', $fileContent2);
$fileContent2 = preg_replace('/<([\\/])av:ОКУД0420502[^>]?>+/', '</av:ОКУД0420502>', $fileContent2);
$fileContent2 = preg_replace('\'<av:\'', '<', $fileContent2);
$fileContent2 = preg_replace('\'</av:\'', '</', $fileContent2);
$xml2 = simplexml_load_string($fileContent2);
$json2 = json_encode($xml2);
$array2 = json_decode($json2, TRUE);
$resultArray1 = [];
$resultArray2 = [];
echo '<pre>';
$diffsArray = [];
groupArraysByRules($array1, $array2);
groupArraysByRules($array2, $array1);
getDiffsBetweenArrays($array1, $array2, 1, 2);
getDiffsBetweenArrays($array2, $array1, 2, 1);
groupArraysByRules($array1, $array2, null, true);
$resultArray = $array1;
if (is_array($resultArray) && count($resultArray)) {
    echo '<table>
    <th>сравниваемый параметр</th>
    <th>значение в файле №1</th>
    <th>значение в файле №2</th><tr>
    <th>имя файла</th>
    <th>' . $_FILES['file1']['name'] . '</th>
    <th>' . $_FILES['file2']['name'] . '</th>
    </tr>
    ';
    if (is_array($diffsArray) && count($diffsArray)) {
        printDiffs($diffsArray, null, null);
    }
    printResult($resultArray);

    echo '</table>';
}
echo '<pre>';

function printResult($resultArray) {

    foreach ($resultArray as $keyRes => $valueRes) {
        if (is_array($valueRes)) {
            if (count($valueRes)) {
                if (!isset($valueRes['diff'])) {
                    if (isset($valueRes['Группировка по ISIN']) ||
                            isset($valueRes['Группировка по сумме']) ||
                            isset($valueRes['Группировка по кадастровому номеру']) ||
                            isset($valueRes['Группировка по ОГРН']) ||
                            isset($valueRes['Группировка по сумме кредиторской задолженности']) ||
                            isset($valueRes['Группировка по стоимости векселя']) ||
                            isset($valueRes['Группировка по Оценочной Стоимости'])
                    ) {
                        if ($keyRes == 'File') {
                            echo '<tr class="ISINheader"><td colspan="3">' . $keyRes . '</td><tr>';
                        } else {
                            $groupReason = '';
                            if (isset($valueRes['Группировка по ISIN']) && $valueRes['Группировка по ISIN'] == true) {
                                $groupReason = 'Группировка по ISIN';
                            } else {
                                if (isset($valueRes['Группировка по сумме']) && $valueRes['Группировка по сумме'] == true) {
                                    $groupReason = 'Группировка по сумме';
                                } else {
                                    if (isset($valueRes['Группировка по кадастровому номеру']) && $valueRes['Группировка по кадастровому номеру'] == true) {
                                        $groupReason = 'Группировка по кадастровому номеру';
                                    } else {
                                        if (isset($valueRes['Группировка по ОГРН']) && $valueRes['Группировка по ОГРН'] == true) {
                                            $groupReason = 'Группировка по ОГРН';
                                        } else {
                                            if (isset($valueRes['Группировка по сумме кредиторской задолженности']) && $valueRes['Группировка по сумме кредиторской задолженности'] == true) {
                                                $groupReason = 'Группировка по сумме кредиторской задолженности';
                                            } else {
                                                if (isset($valueRes['Группировка по стоимости векселя']) && $valueRes['Группировка по стоимости векселя'] == true) {
                                                    $groupReason = 'Группировка по стоимости векселя';
                                                } else {
                                                    if (isset($valueRes['Группировка по Оценочной Стоимости']) && $valueRes['Группировка по Оценочной Стоимости'] == true) {
                                                        $groupReason = 'Группировка по Оценочной Стоимости';
                                                    } else {
//место для новой группировки
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                            echo '<tr class="ISINheader"><td colspan="1">' . $keyRes . '</td><td>' . $groupReason . '</td><td></td><tr>';
                        }
                    }
                    printResult($valueRes);
                } else {



                    if (isset($valueRes['diff'])) {
                        if ($keyRes != 'Группировка по ISIN' && $keyRes != 'Группировка по сумме' && $keyRes != 'Группировка по кадастровому номеру' && $keyRes != 'Группировка по ОГРН' && $keyRes != 'Группировка по сумме кредиторской задолженности' && $keyRes != 'Группировка по стоимости векселя'
                        ) {
                            if ($keyRes == 'File') {
                                echo '<tr class="' . $valueRes['diff'] . '"><td>' . $keyRes . '</td><td>' . (strlen($valueRes['value1']) > 50 ? 'есть прикрепленный файл' : 'нет файла') . '</td><td>' . (strlen($valueRes['value2']) > 50 ? 'есть прикрепленный файл' : 'нет файла') . '</td><tr>';
                            } else {
                                echo '<tr class="' . $valueRes['diff'] . '"><td>' . $keyRes . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
                            }
                        }
                    }
                }
            } else {
                if ($keyRes != 'File') {
                    echo '<tr><td>' . $keyRes . '</td><td>нет значения</td><td>нет значения</td><tr>';
                }
            }
        }
    }
}

function printDiffs($diffsArray) {

    echo '<tr><td colspan="3">НАЧАЛО ТАБЛИЦЫ РАЗЛИЧИЙ</td></tr>';
    foreach ($diffsArray as $keyRes => $valueRes) {
        if (is_array($valueRes)) {
            if (count($valueRes)) {
                if (isset($valueRes['key'])) {


                    echo '<tr class="superDifferent"><td colspan="3">' . $valueRes['key'] . '</td></tr>';
                }
                if (isset($valueRes['valueFromFile1']) && is_array($valueRes['valueFromFile1']) && count($valueRes['valueFromFile1'])) {
                    if (isset($valueRes['valueFromFile2']) && is_array($valueRes['valueFromFile2']) && count($valueRes['valueFromFile2'])) {
                        foreach ($valueRes['valueFromFile1'] as $key1 => $value1) {

                            if ($key1 == 'File') {
                                echo '<tr class="different"><td>' . $valueRes['key'] . '</td><td>' . (!is_array($value1) && strlen($value1) > 50 ? 'есть прикрепленный файл' : 'нет файла') . '</td><td>' . (!is_array($valueRes['valueFromFile2'][$key1]) && strlen($valueRes['valueFromFile2'][$key1]) > 50 ? 'есть прикрепленный файл' : 'нет файла') . '</td><tr>';
                            } else {
                                echo '<tr class="superDifferent"><td>' . $key1 . '</td>' . '<td>';
                                if (is_array($value1)) {
                                    echo 'ТАМ ЕСТЬ ВЛОЖЕННЫЕ СТРОКИ';
                                } else {
                                    echo $value1;
                                }
                                echo '</td>' . '<td>';
                                if (is_array($valueRes['valueFromFile2'][$key1])) {
                                    echo 'ТАМ ЕСТЬ ВЛОЖЕННЫЕ СТРОКИ';
                                } else {
                                    echo $valueRes['valueFromFile2'][$key1];
                                }
                                echo '</td>' . '</tr>';
                            }
                        }
                    }
                }
            } else {
                echo '<tr><td>' . $keyRes . '</td><td>' . $valueRes['valueFromFile1'] . '</td><td>' . $valueRes['valueFromFile2'] . '</td></tr>';
            }
        }
    }
    echo '<tr><td colspan="3">КОНЕЦ ТАБЛИЦЫ РАЗЛИЧИЙ</td></tr>';
}

function createSkeletonCopy(&$resource) {
    $resource = 'нет значения';
}

function getDiffsBetweenArrays($array1, $array2, $fileIdNow = 0, $fileIdSrc = 0) {
    foreach ($array1 as $key1 => $value1) {
        if (is_array($value1)) {
            if (count($value1)) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = $value1;
                    array_walk_recursive($value2, 'createSkeletonCopy');
                    if (isset($fileIdNow) && $fileIdNow > 0) {
                        if (isset($fileIdSrc) && $fileIdSrc > 0) {
                            global $diffsArray;
                            $diffsArray[] = array(
                                'key' => $key1,
                                'valueFromFile' . $fileIdNow => $value1,
                                'valueFromFile' . $fileIdSrc => $value2
                            );
                        }
                    }
                }
                getDiffsBetweenArrays($value1, $value2, $fileIdNow, $fileIdSrc);
            } else {
                $value1[$key1] = 'пустой';
            }
        }
    }
}

function groupArraysByRules(&$array1, $array2, $keyName1 = '', $executeCompare = false) {
    foreach ($array1 as $key1 => &$value1) {
        if (is_array($value1)) {
            if (count($value1)) {
                if (isset($array2[$key1])) {

                    $value2 = $array2[$key1];
                } else {
                    $value2 = $value1;
                    array_walk_recursive($value2, 'createSkeletonCopy');
                }
                if ($key1 == 'РасшифровкиРаздела3') {
                    if (is_array($value1) && count($value1)) {
                        if ($executeCompare !== TRUE) {
                            $value1 = section3decoding($value1);
                        }
                    }
                }
                if ($key1 == 'РасшифровкиРаздела4') {
                    if (is_array($value1) && count($value1)) {
                        if ($executeCompare !== TRUE) {
                            $value1 = section4decoding($value1);
                        }
                    }
                }
                groupArraysByRules($value1, $value2, $key1, $executeCompare);
            } else {

                $value1[$key1] = 'пустой';
            }
        } else {
            if ($executeCompare === TRUE) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = $value1;
                    if (is_array($value2)) {
                        array_walk_recursive($value2, 'createSkeletonCopy');
                    }
                }
                $value1 = compareString($value1, $value2, $key1);
            }
        }
    }
}

function section3decoding($array) {
    $result = [];
    if (is_array($array) && count($array)) {
        foreach ($array as $key1 => $value1) {
            if ($key1 == 'Подраздел2_ЦенБумРосЭмитент' || $key1 == 'Подраздел3ЦенБумИнострЭмит'
            ) {
                if (is_array($value1) && count($value1)) {
                    foreach ($value1 as $keyLEV2 => $valueLEV2) {
                        if (is_array($valueLEV2) && count($valueLEV2)) {
                            foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                                $counter = 0;
                                foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
                                    if (is_array($valueLEV3) && count($valueLEV3)) {
                                        $checkKeysForISIN = FALSE;
                                        if (is_array($valueLEV4) && count($valueLEV4)) {
                                            foreach (array_keys($valueLEV4) as $innerKey => $ISIN) {
                                                if (strpos($ISIN, 'КодISIN') !== FALSE) {
                                                    $checkKeysForISIN = $ISIN;
                                                    $isinCode = $valueLEV4[$checkKeysForISIN];
                                                    $isinCodeOriginal = $valueLEV4[$checkKeysForISIN];
                                                    if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForISIN]])) {
                                                        $counter++;
                                                        $isinCode = $isinCode . '-----' . $counter;
                                                    } else {
//                                                        $counter = 0;
                                                    }
                                                    $value1[$keyLEV2][$keyLEV3][$isinCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                    $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForISIN]]['Группировка по ISIN'] = true;
                                                    $value1[$keyLEV2][$keyLEV3][$isinCode]['Группировка по ISIN'] = true;
                                                    if ($counter > 0) {
                                                        $value1[$keyLEV2][$keyLEV3][$isinCode]['это дубль'] = 'да';
                                                        $value1[$keyLEV2][$keyLEV3][$isinCode]['Очень похож на'] = $isinCodeOriginal;
                                                    }
                                                    if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                        unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                    }
                                                }
                                            }
                                        }
                                        if ($checkKeysForISIN === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                            $counter = 0;
                                            foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                                if (is_array($valueLEV4) && count($valueLEV4)) {
                                                    $checkKeysForISIN = FALSE;
                                                    if (is_array($valueLEV5) && count($valueLEV5)) {
                                                        foreach (array_keys($valueLEV5) as $innerKey => $ISIN) {
                                                            if (strpos($ISIN, 'КодISIN') !== FALSE) {
                                                                $checkKeysForISIN = $ISIN;
                                                                $isinCode = $valueLEV5[$checkKeysForISIN];
                                                                $isinCodeOriginal = $valueLEV5[$checkKeysForISIN];
                                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForISIN]])) {
                                                                    $counter++;
                                                                    $isinCode = $isinCode . '-----' . $counter;
                                                                } else {
//                                                                    $counter = 0;
                                                                }
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForISIN]]['Группировка по ISIN'] = true;
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['Группировка по ISIN'] = true;
                                                                if ($counter > 0) {
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['это дубль'] = 'да';
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['Очень похож на'] = $isinCodeOriginal;
                                                                }

                                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                                }
                                                            } else {
                                                                if (strpos($ISIN, 'Стоимость') !== FALSE && $checkKeysForISIN === FALSE) {
                                                                    $checkKeysForSumm = $ISIN;
                                                                    $summValue = $valueLEV5[$checkKeysForSumm];
                                                                    $summValueOriginal = $valueLEV5[$checkKeysForSumm];
                                                                    if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForSumm]])) {
                                                                        $counter++;
                                                                        $summValue = $summValue . '-----' . $counter;
                                                                    } else {
//                                                                        $counter = 0;
                                                                    }
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$summValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForSumm]]['Группировка по стоимости векселя'] = true;
                                                                    $value1[$keyLEV2][$keyLEV3][$keyLEV4][$summValue]['Группировка по стоимости векселя'] = true;
                                                                    if ($counter > 0) {
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$summValue]['это дубль'] = 'да';
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$summValue]['Очень похож на'] = $summValueOriginal;
                                                                    }

                                                                    if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                        unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                if ($key1 == 'Подраздел1_ДенСредства' || $key1 == 'Подраздел8ДебиторскаяЗадолж' || $key1 == 'Подраздел6ДенежТребования') {
//Группировать по сумме
                    if (is_array($value1) && count($value1)) {
                        foreach ($value1 as $keyLEV2 => $valueLEV2) {
                            if (is_array($valueLEV2) && count($valueLEV2)) {
                                foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                                    $counter = 0;
                                    foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
                                        if (is_array($valueLEV3) && count($valueLEV3)) {
                                            $checkForMoneySummDenSred = FALSE;
                                            if (is_array($valueLEV4) && count($valueLEV4)) {
                                                foreach (array_keys($valueLEV4) as $innerKey => $moneySummDenSred) {
                                                    if (strpos($moneySummDenSred, 'СуммаДенСред') !== FALSE) {
                                                        $checkForMoneySummDenSred = $moneySummDenSred;
                                                        $moneyValue = $valueLEV4[$checkForMoneySummDenSred];
                                                        $moneyValueOriginal = $valueLEV4[$checkForMoneySummDenSred];
                                                        if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySummDenSred]])) {
                                                            $counter++;
                                                            $moneyValue = $moneyValue . '-----' . $counter;
                                                        } else {
//                                                            $counter = 0;
                                                        }
                                                        $value1[$keyLEV2][$keyLEV3][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                        $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySummDenSred]]['Группировка по сумме'] = true;
                                                        $value1[$keyLEV2][$keyLEV3][$moneyValue]['Группировка по сумме'] = true;
                                                        if ($counter > 0) {
                                                            $value1[$keyLEV2][$keyLEV3][$moneyValue]['это дубль'] = 'да';
                                                            $value1[$keyLEV2][$keyLEV3][$moneyValue]['Очень похож на'] = $moneyValueOriginal;
                                                        }
                                                        if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                            unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                        }
                                                    }
//                                                        if (strpos($moneySummDenSred, 'Строка_ДенежТреб') !== FALSE) {
//                                                            var_dump($moneySummDenSred);
//                                                            $checkForMoneySummDenSred = $moneySummDenSred;
//                                                            $moneyValue = $valueLEV4[$checkForMoneySummDenSred];
//                                                            $moneyValueOriginal = $valueLEV4[$checkForMoneySummDenSred];
//                                                            if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySummDenSred]])) {
//                                                                $counter++;
//                                                                $moneyValue = $moneyValue . '-----' . $counter;
//                                                            } else {
//                                                                //                                                            $counter = 0;
//                                                            }
//                                                            $value1[$keyLEV2][$keyLEV3][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
//                                                            $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySummDenSred]]['Группировка по стоимости векселя'] = true;
//                                                            $value1[$keyLEV2][$keyLEV3][$moneyValue]['Группировка по стоимости векселя'] = true;
//                                                            if ($counter > 0) {
//                                                                $value1[$keyLEV2][$keyLEV3][$moneyValue]['это дубль'] = 'да';
//                                                                $value1[$keyLEV2][$keyLEV3][$moneyValue]['Очень похож на'] = $moneyValueOriginal;
//                                                            }
//                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
//                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
//                                                            }
//                                                        }
                                                    }
                                                if (is_array($valueLEV4) && count($valueLEV4)) {
                                                    $counter = 0;
                                                    foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                                        if (is_array($valueLEV4) && count($valueLEV4)) {
                                                            $checkForMoneySummDenSred = FALSE;
                                                            if (is_array($valueLEV5) && count($valueLEV5)) {
                                                                foreach (array_keys($valueLEV5) as $innerKey => $moneySummDenSred) {
                                                                    if (strpos($moneySummDenSred, 'СуммаДенСред') !== FALSE) {
                                                                        $checkForMoneySummDenSred = $moneySummDenSred;
                                                                        $moneyValue = $valueLEV5[$checkForMoneySummDenSred];
                                                                        $moneyValueOriginal = $valueLEV5[$checkForMoneySummDenSred];
                                                                        if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySummDenSred]])) {
                                                                            $counter++;
                                                                            $moneyValue = $moneyValue . '-----' . $counter;
                                                                        } else {
//                                                                            $counter = 0;
                                                                        }
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySummDenSred]]['Группировка по сумме'] = true;
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['Группировка по сумме'] = true;
                                                                        if ($counter > 0) {
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['это дубль'] = 'да';
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['очень похож на'] = $moneyValueOriginal;
                                                                        }

                                                                        if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                            unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                                        }
                                                                    }
                                                                    if (strpos($moneySummDenSred, 'Строка_ДенежТреб') !== FALSE) {
                                                                        var_dump($moneySummDenSred);
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($key1 == 'Подраздел4НедвижИмущ') {
//Группировать по кадастровому номеру
                        if (is_array($value1) && count($value1)) {
                            foreach ($value1 as $keyLEV2 => $valueLEV2) {
                                if (is_array($valueLEV2) && count($valueLEV2)) {
                                    foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                                        $counter = 0;
                                        foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
                                            if (is_array($valueLEV3) && count($valueLEV3)) {
                                                $checkForKadastrNumder = FALSE;
                                                if (is_array($valueLEV4) && count($valueLEV4)) {
                                                    foreach (array_keys($valueLEV4) as $innerKey => $kadastrNumder) {
                                                        if (strpos($kadastrNumder, 'КадастрНомер') !== FALSE) {
                                                            $checkForKadastrNumder = $kadastrNumder;
                                                            $kadastrNumderValue = $valueLEV4[$checkForKadastrNumder];
                                                            $kadastrNumderValueOriginal = $valueLEV4[$checkForKadastrNumder];
                                                            if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForKadastrNumder]])) {
                                                                $counter++;
                                                                $kadastrNumderValue = $kadastrNumderValue . '-----' . $counter;
                                                            } else {
//                                                                $counter = 0;
                                                            }
                                                            $value1[$keyLEV2][$keyLEV3][$kadastrNumderValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                            $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForKadastrNumder]]['Группировка по кадастровому номеру'] = true;
                                                            $value1[$keyLEV2][$keyLEV3][$kadastrNumderValue]['Группировка по кадастровому номеру'] = true;
                                                            if ($counter > 0) {
                                                                $value1[$keyLEV2][$keyLEV3][$kadastrNumderValue]['это дубль'] = 'да';
                                                                $value1[$keyLEV2][$keyLEV3][$kadastrNumderValue]['Очень похож на'] = $kadastrNumderValueOriginal;
                                                            }
                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                            }
                                                        }
                                                    }
                                                }
                                                if ($checkForKadastrNumder === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                                    $counter = 0;
                                                    foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                                        if (is_array($valueLEV4) && count($valueLEV4)) {
                                                            $checkForKadastrNumder = FALSE;
                                                            if (is_array($valueLEV5) && count($valueLEV5)) {
                                                                foreach (array_keys($valueLEV5) as $innerKey => $kadastrNumder) {
                                                                    if (strpos($kadastrNumder, 'КадастрНомер') !== FALSE) {
                                                                        $checkForKadastrNumder = $kadastrNumder;
                                                                        $kadastrNumderValue = $valueLEV5[$checkForKadastrNumder];
                                                                        $kadastrNumderValueOriginal = $valueLEV5[$checkForKadastrNumder];
                                                                        if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForKadastrNumder]])) {
                                                                            $counter++;
                                                                            $kadastrNumderValue = $kadastrNumderValue . '-----' . $counter;
                                                                        } else {
//                                                                            $counter = 0;
                                                                        }
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$kadastrNumderValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForKadastrNumder]]['Группировка по кадастровому номеру'] = true;
                                                                        $value1[$keyLEV2][$keyLEV3][$keyLEV4][$kadastrNumderValue]['Группировка по кадастровому номеру'] = true;
                                                                        if ($counter > 0) {
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$kadastrNumderValue]['это дубль'] = 'да';
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$kadastrNumderValue]['очень похож на'] = $kadastrNumderValueOriginal;
                                                                        }
                                                                        if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                            unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } else {
                        if ($key1 == 'Подраздел7ИноеИмущ') {
//Группировать по ОГРН
                            if (is_array($value1) && count($value1)) {
                                foreach ($value1 as $keyLEV2 => $valueLEV2) {
                                    if (is_array($valueLEV2) && count($valueLEV2)) {
                                        foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                                            $counter = 0;
                                            foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
                                                if (is_array($valueLEV3) && count($valueLEV3)) {
                                                    $checkKeysForOGRN = FALSE;
                                                    if (is_array($valueLEV4) && count($valueLEV4)) {
                                                        foreach (array_keys($valueLEV4) as $innerKey => $OGRN) {
                                                            if (strpos($OGRN, 'ОГРНОбщ') !== FALSE) {
                                                                $checkKeysForOGRN = $OGRN;
                                                                $ogrnCode = $valueLEV4[$checkKeysForOGRN];
                                                                $ogrnCodeOriginal = $valueLEV4[$checkKeysForOGRN];
                                                                if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForOGRN]])) {
                                                                    $counter++;
                                                                    $ogrnCode = $ogrnCode . '-----' . $counter;
                                                                } else {
//                                                                    $counter = 0;
                                                                }
                                                                $value1[$keyLEV2][$keyLEV3][$ogrnCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                                $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForOGRN]]['Группировка по ОГРН'] = true;
                                                                $value1[$keyLEV2][$keyLEV3][$ogrnCode]['Группировка по ОГРН'] = true;
                                                                if ($counter > 0) {
                                                                    $value1[$keyLEV2][$keyLEV3][$ogrnCode]['это дубль'] = 'да';
                                                                    $value1[$keyLEV2][$keyLEV3][$ogrnCode]['Очень похож на'] = $ogrnCodeOriginal;
                                                                }
                                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                                }
                                                            }
                                                        }
                                                    }
                                                    if ($checkKeysForOGRN === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                                        $counter = 0;
                                                        foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                                            if (is_array($valueLEV4) && count($valueLEV4)) {
                                                                $checkKeysForOGRN = FALSE;
                                                                if (is_array($valueLEV5) && count($valueLEV5)) {
                                                                    foreach (array_keys($valueLEV5) as $innerKey => $OGRN) {
                                                                        if (strpos($OGRN, 'ОГРНОбщ') !== FALSE) {
                                                                            $checkKeysForOGRN = $OGRN;
                                                                            $ogrnCode = $valueLEV5[$checkKeysForOGRN];
                                                                            $ogrnCodeOriginal = $valueLEV5[$checkKeysForOGRN];
                                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForOGRN]])) {
                                                                                $counter++;
                                                                                $ogrnCode = $ogrnCode . '-----' . $counter;
                                                                            } else {
//                                                                                $counter = 0;
                                                                            }
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$ogrnCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForOGRN]]['Группировка по ОГРН'] = true;
                                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$ogrnCode]['Группировка по ОГРН'] = true;
                                                                            if ($counter > 0) {
                                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$ogrnCode]['это дубль'] = 'да';
                                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$ogrnCode]['Очень похож на'] = $ogrnCodeOriginal;
                                                                            }
                                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                                            }
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $result[$key1] = $value1;
        }
    }
    return $result;
}

function section4decoding($array) {
    $result = [];

    if (is_array($array) && count($array)) {
        foreach ($array as $key1 => $value1) {
            if (is_array($value1) && count($value1)) {
                foreach ($value1 as $keyLEV2 => $valueLEV2) {
                    if (is_array($valueLEV2) && count($valueLEV2)) {
                        foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                            $counter = 0;
                            foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
                                if (is_array($valueLEV3) && count($valueLEV3)) {
                                    $checkForMoneySumm = FALSE;
                                    if (is_array($valueLEV4) && count($valueLEV4)) {
                                        foreach (array_keys($valueLEV4) as $innerKey => $moneySumm) {
                                            if (strpos($moneySumm, 'КредиторсЗадолж') !== FALSE) {
                                                $checkForMoneySumm = $moneySumm;
                                                $moneyValue = $valueLEV4[$checkForMoneySumm];
                                                $moneyValueOriginal = $valueLEV4[$checkForMoneySumm];
                                                if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySumm]])) {
                                                    $counter++;
                                                    $moneyValue = $moneyValue . '-----' . $counter;
                                                } else {
//                                                    $counter = 0;
                                                }
                                                $value1[$keyLEV2][$keyLEV3][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySumm]]['Группировка по сумме кредиторской задолженности'] = true;
                                                $value1[$keyLEV2][$keyLEV3][$moneyValue]['Группировка по сумме кредиторской задолженности'] = true;
                                                if ($counter > 0) {
                                                    $value1[$keyLEV2][$keyLEV3][$moneyValue]['это дубль'] = 'да';
                                                    $value1[$keyLEV2][$keyLEV3][$moneyValue]['Очень похож на'] = $moneyValueOriginal;
                                                }
                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                }
                                            }
                                        }
                                    }
                                    if ($checkForMoneySumm === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                        $counter = 0;
                                        foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                            if (is_array($valueLEV4) && count($valueLEV4)) {
                                                $checkForMoneySumm = FALSE;
                                                if (is_array($valueLEV5) && count($valueLEV5)) {
                                                    foreach (array_keys($valueLEV5) as $innerKey => $moneySumm) {
                                                        if (strpos($moneySumm, 'КредиторсЗадолж') !== FALSE) {
                                                            $checkForMoneySumm = $moneySumm;
                                                            $moneyValue = $valueLEV5[$checkForMoneySumm];
                                                            $moneyValueOriginal = $valueLEV5[$checkForMoneySumm];
                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySumm]])) {
                                                                $counter++;
                                                                $moneyValue = $moneyValue . '-----' . $counter;
                                                            } else {
//                                                                $counter = 0;
                                                            }
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySumm]]['Группировка по сумме кредиторской задолженности'] = true;
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['Группировка по сумме кредиторской задолженности'] = true;
                                                            if ($counter > 0) {
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['это дубль'] = 'да';
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['очень похож на'] = $moneyValueOriginal;
                                                            }
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4]['Содержит Суммы'][$moneyValue] = $valueLEV5[$checkForMoneySumm];

                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $result[$key1] = $value1;
        }
    }
    return $result;
}

function srtringsIdentical($str1, $str2) {
    $result = false;
    $encodedCurrentVocab = getExternalVocab();
    foreach ($encodedCurrentVocab as $ruleIndex => $rule) {
        if ($result == FALSE) {
            $pattern = array('/,/', '/\\s{1,}/', '/«/', '/»/', '/"/');
            $replacement = array('.', '', '"', '"', '');
            $str1 = trim($str1);
            $str2 = trim($str2);
            $str1 = mb_strtolower($str1);
            $str2 = mb_strtolower($str2);
            $str1 = preg_replace($pattern, $replacement, $str1);
            $str2 = preg_replace($pattern, $replacement, $str2);

            $ruleSrt1 = $rule[0];
            $ruleSrt2 = $rule[1];
            $ruleSrt1 = mb_strtolower($ruleSrt1);
            $ruleSrt2 = mb_strtolower($ruleSrt2);
            $ruleSrt1 = preg_replace($pattern, $replacement, $ruleSrt1);
            $ruleSrt2 = preg_replace($pattern, $replacement, $ruleSrt2);

            if ($ruleSrt1 == $str1) {
                if ($str2 == $ruleSrt2) {
                    $result = true;
                }
            }
            if ($result == FALSE) {
                if ($ruleSrt2 == $str1) {
                    if ($str2 == $ruleSrt1) {
                        $result = true;
                    }
                }
            }
            if ($result == FALSE) {
                if ($ruleSrt1 == $str2) {
                    if ($str1 == $ruleSrt2) {
                        $result = true;
                    }
                }
                if ($result == FALSE) {
                    if ($ruleSrt2 == $str2) {
                        if ($str1 == $ruleSrt1) {
                            $result = true;
                        }
                    }
                }
            }
        }
    }
    if ($str1 == $str2) {
        $result = true;
    }
    return $result;
}

function compareString($string1, $string2, $key1) {
    $result = array();
    $result['value1'] = $string1;
    $result['value2'] = $string2;
    $result['diff'] = 'different';
    $parsingError = FALSE;
    if (is_array($string1)) {
        if (count($string1)) {
            $parsingError = 'В файле 1 есть строка "' . $key1 . '" у которой есть вложенные элементы';
        } else {
            $parsingError = 'В файле 1 есть пустая строка "' . $key1 . '" которая распарсилась как массив';
        }
        $result['value1'] = $parsingError;
    }
    if (is_array($string2)) {
        if (count($string2)) {
            $parsingError = 'В файле 2 есть строка "' . $key1 . '" у которой есть вложенные элементы';
        } else {
            $parsingError = 'В файле 2 есть пустая строка "' . $key1 . '" которая распарсилась как массив';
        }
        $result['value2'] = $parsingError;
    }
    $result['parsingErrors'] = $parsingError;
    if ($result['parsingErrors'] === FALSE) {


        $isIdentical = srtringsIdentical($string1, $string2);
        if ($isIdentical === true) {
            $result['diff'] = 'identical';
        }
    } else {
        $result['diff'] = 'impossible';
    }
    return $result;
}

die();
