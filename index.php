<head>
    <meta charset="utf-8"/>
    <link href="resources/bootstrap-3.3.7/css/bootstrap.min.css" rel="stylesheet">
    <script src="resources/jquery-3.2.1.min.js"></script>
    <script src="resources/bootstrap-3.3.7/js/bootstrap.min.js"></script>
</head>
<!--<p>Это резервный сервер. Не гарантируется, что здесь всё будет работать корректно.</p>
<p>Слушайтесь Инночку и идите <a href="http://compare.gulkinnos.ru/">на нормальный сервер</a>
</p>-->
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
        font-size: 1.3rem;
    }
    tr {
        width: 100%
    }
    th.containsGroup{
        text-align: left;
        background-color: rgba(255, 255, 0, 0.45);
    }
    #version{
        top: 0;
        right: 0;
        font-size: 0.7rem;
    }
</style>
<body>
    <div class="row">
        <div class="row">
            <!--<h1>Сравнение .xtdd</h1>-->
            <div class="row col-lg-7">
                <h1>Сравнение .xtdd</h1>
                <form method="POST" name="111" enctype="multipart/form-data">
                    <input type="hidden" name="startComparison" />
                    <div class="form-group col-lg-12">
                        <label for="file1input">файл с данными от УК
                            <input id="file1input" type="file" name="file1" placeholder="файл с данными от УК"></label>
                    </div>
                    <div class="form-group col-lg-12">
                        <label for="file2input">файл с данными СПЕЦДЕПА
                            <input id="file2input" type="file" name="file2" placeholder="файл с данными СПЕЦДЕПА"></label>
                    </div>
                    <div class="form-group col-lg-8">
                        <button class="btn btn-success" id="submitInput" type="submit">Запустить проверку</button>
                    </div>
                    <div class="form-group col-lg-4">
                        <a href="/manageVocab.php" target="_blank">
                            <div class="btn btn-info">Открыть словарь соответствий</div>
                        </a>
                    </div>
                </form>
            </div>
            <div class="row col-lg-5">
                <div id="version" class="col-lg-12">v 2.01 14.09.2017
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
                    <br>Находится в режиме <a href="https://ru.wikipedia.org/wiki/Бета-тестирование" target="_blank">бета-тестирования</a>.
                </div>
            </div>
        </div>
        <div class="row col-lg-12">
            <?php
            if (!isset($_POST['startComparison'])) {
                die('<div class="row col-lg-12"> Привет! Выбери два файла и нажми зеленую кнопку</div>');
            }
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
                die('Надо выбрать два файла! Не выбран файл 1.');
            }

            if (!$filename2 == '') {
                if (file_exists($filename2)) {

                    $fileContent2 = file_get_contents($filename2);
                } else {
                    die('Файл 2 выбран, но не загрузился на сервер. Проверьте права или что-то ещё на серваке');
                }
            } else {
                die('Надо выбрать два файла! Не выбран файл 2.');
            }
            ?>
        </div>
        <?php
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

        $headers->getTotalParents($xmlObject1);
        $headers->getTotalParents($xmlObject2);

        $headers->fileNumber = 1;
        $headers->getFullHeaders('', $xmlObject1);

        $headers->strangeCounter3_9 = 0;
        $headers->fileNumber = 2;
        $headers->getFullHeaders('', $xmlObject2);

        $headers->compareValues();
        ?><table>
            <thead>
                <tr class="header">
                    <th>Название поля</th>
                    <th><?= $_FILES['file1']['name'] ?></th>
                    <th><?= $_FILES['file2']['name'] ?></th>
                </tr>
            </thead>
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
    </div>
</body>