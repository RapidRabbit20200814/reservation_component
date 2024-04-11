<?php
// ====================================
//   cron用のDBアクセス処理
// ====================================


$supabaseUrl = "https://tchxpyzlvuppoglfodew.supabase.co";
$supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InRjaHhweXpsdnVwcG9nbGZvZGV3Iiwicm9sZSI6ImFub24iLCJpYXQiOjE2OTU2MDQ0NTEsImV4cCI6MjAxMTE4MDQ1MX0.zsWNVXkR5fDAsEFloY9kqoB2m7UVWjZMVkRQ3y4GLn4";

// データの作成
$data = array(
    "result" => true
);

// クエリパラメータの設定
$queryParams = http_build_query(array(
    "apikey" => $supabaseKey,
));

// APIエンドポイントの設定
$apiEndpoint = $supabaseUrl . "/rest/v1/log?" . $queryParams;

// cURLを使用してデータを挿入
$ch = curl_init($apiEndpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));

$response = curl_exec($ch);

// cURLセッションを閉じる
curl_close($ch);

// レスポンスの表示
echo $response;
?>
