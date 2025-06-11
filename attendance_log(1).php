<?php
$rawdata = file_get_contents("php://input");
file_put_contents("test_input.txt", "Raw data: " . $rawdata);
echo "Data received. Check test_input.txt";
?>