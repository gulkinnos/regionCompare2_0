<?php

/**
 * @author Aleksandr Golubev aka gulkinnos <gulkinnos@gmail.com>
 */
class Logger {

    public function logAccessIntoFile() {
        $dateTime = date('Y-m-d H:i:s');
        $message = 'Просто зашли';
        if (isset($_POST['startComparison'])) {
            $message = 'Запустили сравнение';
            if (isset($_FILES['file1']['name'])) {
                $message .= ' Файл 1: ' . $_FILES['file1']['name'];
            } else {
                $message .= ' Файл 1: не указан';
            }
            if (isset($_FILES['file2']['name'])) {
                $message .= ' Файл 2: ' . $_FILES['file2']['name'];
            } else {
                $message .= ' Файл 2: не указан';
            }
        }
        $fileName = 'logs/access.php';
        $file = fopen($fileName, 'a+');
        $stringToLog = mb_convert_encoding($dateTime . ' ' . $message . "<br>\r\n", 'UTF-8');
        fwrite($file, $stringToLog);
        fclose($file);
    }

}
