<?php
$output = [];
$return_var = 0;
exec('"python.exe" --version 2>&1', $output, $return_var);
print_r($output);
?>