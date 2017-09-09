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

groupArraysByRules($array1, $array2);

function groupArraysByRules(&$array1, $array2, $keyName1 = '', $executeCompare = false) {

    foreach ($array1 as $key1 => &$value1) {
        if (is_array($value1)) {
            if (count($value1)) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = [];
                    foreach ($value1 as $keyNF => $valueNF) {
                        if (is_array($valueNF) && count($valueNF)) {
                            foreach ($valueNF as $keyNF2 => $valueNF2) {
                                $value2[$keyNF][$keyNF2] = $valueNF;
                            }
                        } else {
                            
                            
                            
                        }
                    }
                }
                if ($key1 == 'РасшифровкиРаздела3') {
                    if (is_array($value1) && count($value1)) {
                        if ($executeCompare !== TRUE) {
                            $value1 = section3decoding($value1);
                        }
                    }
                }
                if ($key1 == 'Подраздел8ДебиторскаяЗадолж') {
                    if (is_array($value1) && count($value1)) {
                        if ($executeCompare !== TRUE) {
                            $value1 = section8decoding($value1);
                        }
                    }
                }
                groupArraysByRules($value1, $value2, $key1, $executeCompare);
            } else {
//                $value1['empty'] = 'пустой';
            }
        } else {
            if ($executeCompare === TRUE) {
                if (isset($array2[$key1])) {
                    $value2 = $array2[$key1];
                } else {
                    $value2 = [];
                }
                var_dump($value1);
                var_dump($value2);
                var_dump($key1);
                echo '<hr>';
                $value1 = compareString($value1, $value2, $key1);
            }
        }
    }
}



//array_walk_recursive($array1, compareArrays);









function compareArrays($string,$key){
    
var_dump($key);
var_dump($string);
echo '<hr>';
    
    
}