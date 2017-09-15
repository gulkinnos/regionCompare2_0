<?php

if (isset($_GET['pass']) && md5($_GET['pass']) == '6f0365c08ba966e60489c6babd6cfff0') {
    echo 'Доступ разрещён';
} else {
    die('Доступ запрещён');
}
?>

