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

//av:ОКУД0420502_2_16_3_1

libxml_use_internal_errors(true);
$fileContent1 = preg_replace('/(<av:ОКУД[^[:space:]]*).([^>]*)/', '$1', $fileContent1);
$xmlObject1 = new SimpleXMLElement($fileContent1);
if (!$xmlObject1) {
    echo "Ошибка загрузки XML\n";
    foreach (libxml_get_errors() as $error) {
        echo "\t", $error->message;
    }
}
$fileContent2 = preg_replace('/(<av:ОКУД[^[:space:]]*).([^>]*)/', '$1', $fileContent2);
$xmlObject2 = new SimpleXMLElement($fileContent2);
if (!$xmlObject2) {
    echo "Ошибка загрузки XML\n";
    foreach (libxml_get_errors() as $error) {
        echo "\t", $error->message;
    }
}
require_once './classes/Headers.php';
$headers=new Headers();
$headers->fileNumber=1;
$headers->getFullHeaders('',$xmlObject1);
$headers->fileNumber=2;
$headers->getFullHeaders('',$xmlObject2);
die(var_dump($headers));

$fullHeaders=getFullHeaders(array(), $xmlObject1);
die(var_dump($fullHeaders));



die(var_dump($fullHeaders));

die(var_dump($xmlObject1->children('av')));



if (!$filename2 == '') {
    if (file_exists($filename2)) {

        $fileContent2 = file_get_contents($filename2);
    } else {
        die('Файл 2 выбран, но не загрузился на сервер. Проверьте права или что-то ещё на серваке');
    }
} else {
    die('Не выбран файл 2');
}
