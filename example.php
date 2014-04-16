<?php
require_once 'Krypton.php';

$data = Krypton::convertDirectory('path/to/selenium/testcases');
file_put_contents('test.php', $data);
