<?php
$connect = mysqli_connect('localhost', 'root', '', 'test');
if (!$connect) {
    die("Ошибка подключения: " . mysqli_connect_error());
}