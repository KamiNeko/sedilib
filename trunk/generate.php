<?php

header('Content-type: application/json');

require_once('seqlib/sequence_parser.php');

$text = urldecode($_POST['seqdia_code']);
$text = str_replace("\\\\n", "\x", $text);
$text = str_replace("\n", "\r\n", $text);
$text = str_replace("\x", "\n", $text);

//echo json_encode(array("img" => $text));


$text = str_replace("\\'", "'", $text);

$md5 = md5($text);
$file_name = $md5.'.png';
$errors = array();
$error_one_line = "";

if (!file_exists($file_name)) {
    $parser = new SequenceParser($text, $errors);
    $parser->generateImage($file_name);
    
    $error_one_line = implode("<br>", $errors);
    
    echo json_encode(array("img" => $file_name, "errors" => $error_one_line));
}
else {
    echo json_encode(array("img" => $file_name));
}

?>