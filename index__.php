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
$resultArray1 = [];
$resultArray2 = [];

echo '<pre>';
//die();
//var_dump($array2);
$mergedArray = array_merge_recursive($array1, $array2);

var_dump($mergedArray);


echo '<table>
    <th>сравниваемый параметр</th>
    <th>значение в файле №1</th>
    <th>значение в файле №2</th>';
printResult($mergedArray);
echo '</table>';

die();
groupArraysByRules($array1, $array2);
groupArraysByRules($array2, $array1);
die();
groupArraysByRules($array1, $array2, null, true);
$resultArray = $array1;

echo '<pre>';

//var_dump($array1);
//var_dump($array2);

function printResult($resultArray) {

    foreach ($resultArray as $keyRes => $valueRes) {
//        var_dump($valueRes['header']);
        if (is_array($valueRes)) {
            if (count($valueRes)) {
                printResult($valueRes);
                if (isset($valueRes[0]) && isset($valueRes[1])) {

                    $class = 'different';
                    if (!is_array($valueRes[0]) && !is_array($valueRes[1])) {
                        if ($valueRes[0] == $valueRes[1]) {
                            $class = 'identical';
                        } else {
                            if (trim($valueRes[0]) == trim($valueRes[1])) {
                                $class = 'identical';
                            } else {

                                $stringToFloat1 = str_replace(',', '.', $valueRes[0]);
                                $stringToFloat2 = str_replace(',', '.', $valueRes[1]);
                                if ($stringToFloat1 == $stringToFloat2)
                                    $class = 'identical';
                            }
                        }
                        echo '<tr class="' . $class . '"><td>' . $keyRes . '</td><td>' . $valueRes[0] . '</td><td>' . $valueRes[1] . '</td><tr>';
                    } else {
                        if (is_array($valueRes[0])) {
                            foreach ($valueRes[0] as $key => $value) {

                                echo '<tr class="' . $class . '"><td>' . $key . '</td><td>' . $value . '</td><td>XXXXXXX</td><tr>';
                            }
                        }
                        if (is_array($valueRes[1])) {
                            foreach ($valueRes[1] as $key => $value) {
                                echo '<tr class="' . $class . '"><td>' . $key . '</td><td>XXXXXXX</td><td>' . $value . '</td><tr>';
                            }
                        }
                    }
                }

//                var_dump($valueRes);
            } else {
                
            }
        }
    }
}

/*
  function printResult($resultArray) {

  foreach ($resultArray as $keyRes => $valueRes) {
  //        var_dump($valueRes['header']);
  if (!isset($valueRes['isNotHeader'])) {

  //            echo '<tr><td colspan="3">' . $keyRes . '</td></tr>';
  }
  if (is_array($valueRes)) {
  if (count($valueRes)) {
  if (!isset($valueRes['diff'])) {
  if (isset($valueRes['isISIN'])) {
  echo '<tr class="ISINheader"><td colspan="3">' . $keyRes . '</td><tr>';
  }
  printResult($valueRes);
  } else {



  if (isset($valueRes['diff'])) {
  if ($keyRes != 'isISIN') {
  echo '<tr class="' . $valueRes['diff'] . '"><td>' . $keyRes . '</td><td>' . $valueRes['value1'] . '</td><td>' . $valueRes['value2'] . '</td><tr>';
  }
  }
  //                    }
  }
  } else {
  echo '<tr><td>' . $keyRes . '</td><td>нет значения</td><td>нет значения</td><tr>';
  }
  }
  }
  }
 */

function array_diff_assoc_recursive($array1, $array2) {
    $difference = array();
    foreach ($array1 as $key => $value) {
        if (is_array($value)) {
            if (!isset($array2[$key]) || !is_array($array2[$key])) {
                $difference[$key] = $value;
            } else {
                $new_diff = array_diff_assoc_recursive($value, $array2[$key]);
                if (!empty($new_diff))
                    $difference[$key] = $new_diff;
            }
        } else if (!array_key_exists($key, $array2) || $array2[$key] !== $value) {
            $difference[$key] = $value;
        }
    }
    return $difference;
}

function groupArraysByRules(&$array1, $array2, $keyName1 = '', $executeCompare = false) {
    foreach ($array1 as $key1 => &$value1) {
        $selfValue = 'XXXXX';
        $oppositeValue = 'XXXXX';
        $difference = 'different';


        if (is_array($value1)) {
            if (!count($value1)) {
                $selfValue = 'XXXXX';

//            die(var_dump($key1));
                if (isset($array2[$key1])) {

                    if (is_array($array2[$key1])) {
                        if (!count($array2[$key1])) {
                            
                        } else {
                            $oppositeValue = $array2[$key1];
                        }
                    }
                }


                $value1 = array(
                    'selfValue' => $selfValue,
                    'oppositeValue' => $oppositeValue
                );
            } else {
                $selfValue = $value1;
            }
            die(var_dump($value1));

//            die(var_dump($value1));
        }
    }
}

function compareArrays($array1, $array2, $keyName1 = '', $executeCompare = false) {
    foreach ($array1 as $key1 => $value1) {
        if (is_array($value1)) {
            if (count($value1)) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = [];
                    foreach ($value1 as $keyNF => $valueNF) {
                        if (is_array($valueNF) && count($valueNF)) {
                            foreach ($valueNF as $keyNF2 => $valueNF2) {

                                $value2[$keyNF][$keyNF2] = 'VVVVV';
                            }
                        } else {
                            $value2[$keyNF] = 'XXXXX';
                        }
                    }
                }


                compareArrays($value1, $value2, $key1, $executeCompare);
            } else {

                $value1['empty'] = 'пустой';
            }
        } else {
            if ($executeCompare === TRUE) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = [];
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
//                                                                var_dump($valueLEV5);
//                                                    echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                    echo '<br>';
                                                } else {
                                                    $counter = 0;
                                                }
                                                $value1[$keyLEV2][$keyLEV3][$isinCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkKeysForISIN]]['isISIN'] = true;
                                                if ($counter > 0) {
                                                    $value1[$keyLEV2][$keyLEV3][$isinCode]['isDouble'] = true;
                                                    $value1[$keyLEV2][$keyLEV3][$isinCode]['looksLike'] = $isinCodeOriginal;
                                                }
                                                echo $keyLEV3;
                                                $value1[$keyLEV2][$keyLEV3]['containsISIN'][$isinCode] = $valueLEV4[$checkKeysForISIN];
                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                }
                                            }
//                                            var_dump($value1[$keyLEV2][$keyLEV3]);
//                                                asort($value1[$keyLEV2][$keyLEV3]);
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
//                                                                var_dump($valueLEV5);
//                                                                echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                                echo '<br>';
                                                            } else {
                                                                $counter = 0;
                                                            }
//                                                            $checkKeysForISIN=$checkKeysForISIN.'-----'.$counter;
//                                                            }
//                                                                var_dump($valueLEV5[$checkKeysForISIN]);
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
//                                                            die(var_dump($value1[$keyLEV2][$keyLEV3][$keyLEV4]));
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkKeysForISIN]]['isISIN'] = true;
                                                            if ($counter > 0) {
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['isDouble'] = true;
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$isinCode]['looksLike'] = $isinCodeOriginal;
                                                            }
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4]['containsISIN'][$isinCode] = $valueLEV5[$checkKeysForISIN];

                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                            }
                                                        }
                                                    }
//                                                            asort($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
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
//            print_r($result);
        }
    }
    return $result;
}

function section8decoding($array) {
    $result = [];


//    return $result;
    if (is_array($array) && count($array)) {
        foreach ($array as $key1 => $value1) {
            if (is_array($value1) && count($value1)) {
                foreach ($value1 as $keyLEV2 => $valueLEV2) {
//                    asort($valueLEV2);
                    if (is_array($valueLEV2) && count($valueLEV2)) {
                        foreach ($valueLEV2 as $keyLEV3 => $valueLEV3) {
                            $counter = 0;
                            foreach ($valueLEV3 as $keyLEV4 => $valueLEV4) {
//                    var_dump($valueLEV4);
                                if (is_array($valueLEV3) && count($valueLEV3)) {
                                    $checkForMoneySumm = FALSE;
                                    if (is_array($valueLEV4) && count($valueLEV4)) {
                                        foreach (array_keys($valueLEV4) as $innerKey => $moneySumm) {
                                            if (strpos($moneySumm, 'СуммаДенСредств') !== FALSE) {
                                                $checkForMoneySumm = $moneySumm;
                                                $moneyValue = $valueLEV4[$checkForMoneySumm];
                                                $moneyValueOriginal = $valueLEV4[$checkForMoneySumm];
                                                if (isset($value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySumm]])) {
                                                    $counter++;
                                                    $moneyValue = $moneyValue . '-----' . $counter;
//                                                                var_dump($valueLEV5);
//                                                    echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                    echo '<br>';
                                                } else {
                                                    $counter = 0;
                                                }
                                                $value1[$keyLEV2][$keyLEV3][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4];
                                                $value1[$keyLEV2][$keyLEV3][$valueLEV4[$checkForMoneySumm]]['isISIN'] = true;
                                                if ($counter > 0) {
                                                    $value1[$keyLEV2][$keyLEV3][$moneyValue]['isDouble'] = true;
                                                    $value1[$keyLEV2][$keyLEV3][$moneyValue]['looksLike'] = $moneyValueOriginal;
                                                }
//                                                echo $keyLEV3;
                                                $value1[$keyLEV2][$keyLEV3]['containsISIN'][$moneyValue] = $valueLEV4[$checkForMoneySumm];
                                                if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4])) {
                                                    unset($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
                                                }
                                            }
//                                            var_dump($value1[$keyLEV2][$keyLEV3]);
//                                                asort($value1[$keyLEV2][$keyLEV3]);
                                        }
                                    }
                                    if ($checkForMoneySumm === FALSE && is_array($valueLEV4) && count($valueLEV4)) {
                                        $counter = 0;
                                        foreach ($valueLEV4 as $keyLEV5 => $valueLEV5) {
                                            if (is_array($valueLEV4) && count($valueLEV4)) {
                                                $checkForMoneySumm = FALSE;
                                                if (is_array($valueLEV5) && count($valueLEV5)) {
                                                    foreach (array_keys($valueLEV5) as $innerKey => $moneySumm) {
                                                        if (strpos($moneySumm, 'КодISIN') !== FALSE) {
                                                            $checkForMoneySumm = $moneySumm;
                                                            $moneyValue = $valueLEV5[$checkForMoneySumm];
                                                            $moneyValueOriginal = $valueLEV5[$checkForMoneySumm];
                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySumm]])) {
                                                                $counter++;
                                                                $moneyValue = $moneyValue . '-----' . $counter;
//                                                                var_dump($valueLEV5);
//                                                                echo $counter;
//                                                                echo $checkKeysForISIN;
//                                                                echo '<br>';
                                                            } else {
                                                                $counter = 0;
                                                            }
//                                                            $checkKeysForISIN=$checkKeysForISIN.'-----'.$counter;
//                                                            }
//                                                                var_dump($valueLEV5[$checkKeysForISIN]);
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue] = $array[$key1][$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5];
//                                                            die(var_dump($value1[$keyLEV2][$keyLEV3][$keyLEV4]));
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4][$valueLEV5[$checkForMoneySumm]]['isISIN'] = true;
                                                            if ($counter > 0) {
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['isDouble'] = true;
                                                                $value1[$keyLEV2][$keyLEV3][$keyLEV4][$moneyValue]['looksLike'] = $moneyValueOriginal;
                                                            }
                                                            $value1[$keyLEV2][$keyLEV3][$keyLEV4]['containsISIN'][$moneyValue] = $valueLEV5[$checkForMoneySumm];

                                                            if (isset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5])) {
                                                                unset($value1[$keyLEV2][$keyLEV3][$keyLEV4][$keyLEV5]);
                                                            }
                                                        }
                                                    }
//                                                            asort($value1[$keyLEV2][$keyLEV3][$keyLEV4]);
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
//            print_r($result);
        }
    }
    return $result;
}

function compareString($string1, $string2, $key1) {
    $result = array();

//    var_dump($string1,$string2,$key1);

    $result['value1'] = $string1;
    $result['value2'] = $string2;
//    $result['isNotHeader'] = true;

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
        if (trim($string1) == trim($string2)) {
            $result['diff'] = 'identical';
        } else {

            $stringToFloat1 = str_replace(',', '.', $string1);
            $stringToFloat2 = str_replace(',', '.', $string2);
            if ($stringToFloat1 == $stringToFloat2)
                $result['diff'] = 'identical';
        }
    } else {
        $result['diff'] = 'impossible';
    }
//    echo '<hr>';
//    var_dump($string1, $string2, $key1, $result['diff'], $result['parsingErrors']);
//    echo '<hr>';
//    echo '<hr>';
    return $result;
}

die();





