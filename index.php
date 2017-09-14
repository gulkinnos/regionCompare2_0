<head>
    <meta charset="utf-8"/>
</head>
<!--<p>Тут не работает</p>
<p>Слушайтесь Инночку и идите <a href="http://compare.gulkinnos.ru/">на нормальный сервер</a>
</p>-->
<?php
//die();
?>
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
    .nodeName{
        width: 40%
    }
    .value{
        width: 30%
    }
    table{
        border-collapse:  collapse;
    }
    tr {
        width: 100%
    }
    th.containsGroup{
        text-align: left;
        background-color: rgba(255, 255, 0, 0.45);
    }
    /*    table > td {
            width: 30%;
        }*/
    #version{
        background-color: lightgray;
        width: 30%;
        position: absolute;
        top: 0;
        right: 0;
        font-size: 0.7rem;
    }
</style>
<div id="version">v 2.01 14.09.2017
    <br>13.09.2016 Пофиксил ошибку группировки по ISIN. Если уже сгруппированно, то не группируется по сумме.
    <br>11.09.2017 Всё переделал. Работает в 30000 / 0.385 раз быстрее. Разбирает рекурсивно, независимо от структуры и вложенности.
    <br>Реализованы группировки по:
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол7_Таб2КодISIN'
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол7_Таб8КодISIN'
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол6_Таб3КодISIN' и по 'av:Кол3_Таб3ОГРН'
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол7_Таб34_2ОГРНДолжника' и по 'av:Кол8_Таб34_2СуммаДенСредств'
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол8_Таб1_1СуммаДенСред'
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол3_Таб27ОГРНОбщ'
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол3_Таб9ОГРНВекселедателя' и по номеру по порядку в файле
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол6_Таб13КодISIN
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол2_Таб26_1НомерКредитДог
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол2_Таб26_2НомерКредитДог
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол8_Таб34_1ФактСуммаЗадолж
    <br>&nbsp;&nbsp;&nbsp;&nbsp;'av:Кол8_Таб34_2СтоимРасчетАктивов
    <br>Находится в режиме бета-тестирования.
    <br>Я люблю choko pie..
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
set_time_limit(3600);
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

echo 'Печеньки: ' . '9 и 1/2' . '<br><br>';
echo 'Бесплатная пробная версия. Если Вам нравится наш сайт и Вы хотите пользоваться им дальше, переведите указаное количество печенек разработчику ))'. '<br><br>';
libxml_use_internal_errors(true);
$fileContent1 = preg_replace('/(<av:ОКУД[^[:space:]]*).([^>]*)/', '$1', $fileContent1);
$fileContent1 = preg_replace('/(<av:Files).*(<\/av:Files>)/ms', '', $fileContent1);
$xmlObject1 = new SimpleXMLElement($fileContent1);
if (!$xmlObject1) {
    echo "Ошибка загрузки XML\n";
    foreach (libxml_get_errors() as $error) {
        echo "\t", $error->message;
    }
}
$fileContent2 = preg_replace('/(<av:ОКУД[^[:space:]]*).([^>]*)/', '$1', $fileContent2);
$fileContent2 = preg_replace('/(<av:Files).*(<\/av:Files>)/ms', '', $fileContent2);
$xmlObject2 = new SimpleXMLElement($fileContent2);
if (!$xmlObject2) {
    echo "Ошибка загрузки XML\n";
    foreach (libxml_get_errors() as $error) {
        echo "\t", $error->message;
    }
}
require_once './classes/Headers.php';
$headers = new Headers();
$headers->fileNumber = 1;
$headers->getFullHeaders('', $xmlObject1);
$headers->strangeCounter3_9 = 0;
$headers->fileNumber = 2;
$headers->getFullHeaders('', $xmlObject2);
$headers->compareValues();
?><table>
    <tr class="header">
        <th>Название поля</th>
        <th><?= $_FILES['file1']['name'] ?></th>
        <th><?= $_FILES['file2']['name'] ?></th>
    </tr>
    <?php foreach ($headers->fullHeaders as $nodeName => $nodeValues) { ?>
        <?php
        if (isset($nodeValues['containsISINs'])) {
            echo '<th class="containsGroup" colspan="3">' . $nodeValues['containsISINs'] . '</th>';
        }
        ?>
        <tr class="<?php
        if (isset($nodeValues['difference'])) {
            echo $nodeValues['difference'];
        }
        ?>">
            <td class="nodeName"><?php
                if (isset($nodeValues['nodeName'])) {
                    echo $nodeValues['nodeName'];
                }
                ?> </td>
            <td class="value"><?php
                if (isset($nodeValues[1])) {
                    echo $nodeValues[1];
                }
                ?></td>
            <td class="value"><?php
                if (isset($nodeValues[2])) {
                    echo $nodeValues[2];
                }
                ?></td>
        </tr>
        <?php
    }
    ?>
</table>

