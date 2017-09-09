<style>
    .vocabulary {
        border-collapse: collapse;
    }
    .vocabulary td{
        border: solid black 1px;
        width: 50%;
    }
</style>
<?php
echo 'Это словарь соответствий. Последнее изменение было: '. date('d.m.Y в H:i:s',preg_replace('/\\D/', '',basename(getLastFilename()))).'<br>';
echo '<pre>';
//die(var_dump($_GET));
if (isset($_GET['resaveVocab']) && $_GET['resaveVocab'] == 'yes') {
    if (isset($_POST['vocabString']) && trim(strip_tags($_POST['vocabString'])) != '') {
        $currentContent = trim(strip_tags($_POST['vocabString']));
        $destFileName = getLastFilename();
        $destFileName = pathinfo($destFileName)['dirname'] . DIRECTORY_SEPARATOR . 'currentVocab' . time() . '.' . pathinfo($destFileName)['extension'];
        $currentContent = preg_replace('\'\\t\'', '~', $currentContent);
        setContentToLastFile($destFileName, $currentContent);
    }
}
?>
<form method="POST" action="\manageVocab.php?resaveVocab=yes" >
    <textarea name="vocabString" style="width: 50%;height: 80%" ><?php
        $vocab = getContentFromLastFile(getLastFilename());
        print_r($vocab);
        ?></textarea><br>
    <button type="submit">Перезаписать словарь</button>
    <br>
    Или в виде таблицы:<br>
    <table class="vocabulary">
        <?php
        if ($vocab != '') {
            $vocabArray = csv_to_array(getLastFilename(), '~');
            if (is_array($vocabArray) && count($vocabArray)) {
                foreach ($vocabArray as $row) {
                    echo '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
                }
            }
        }
        ?>   
    </table>

</form>
<?php

function setContentToLastFile($fileName, $content) {

    if (trim($fileName != '') && trim($content) != '') {
        $content = trim(strip_tags($content));
        file_put_contents($fileName, $content);
    }
}

function getLastFilename() {
    $lastFileName = '';
    $currentVocabFileNames = glob('./vocab/*.csv');
    if (is_array($currentVocabFileNames) && count($currentVocabFileNames)) {
        $lastFileName = array_pop($currentVocabFileNames);
    }
    return $lastFileName;
}

function getContentFromLastFile($fileName) {
    $resultString = '';
    if (trim($fileName) != '') {
        $resultString = file_get_contents($fileName);
    }
    return $resultString;
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
