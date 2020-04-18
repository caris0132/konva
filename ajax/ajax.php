<?php

$act = $_GET['act'];

$content_json = file_get_contents('../data/' . $act  . '.json');

echo $content_json;
