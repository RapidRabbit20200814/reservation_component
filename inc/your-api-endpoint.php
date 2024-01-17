<?php
// サーバーサイドのPHPコード
$responseData = array("message" => "This is a JSON response.");
header("Content-Type: application/json");
echo json_encode($responseData);
?>
