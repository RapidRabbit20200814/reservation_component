<?php
// ====================================
//   cron用のDBアクセス処理
// ====================================

// ====================================
//   LOGデータの挿入
// ====================================

$supabaseUrl = "https://lpwlxqfealimlwttkamp.supabase.co";
$supabaseKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Imxwd2x4cWZlYWxpbWx3dHRrYW1wIiwicm9sZSI6ImFub24iLCJpYXQiOjE3MTEwNzAwNDMsImV4cCI6MjAyNjY0NjA0M30.VEai5wR0shK8KiWA_tzqXvGAkOO39uEx66yfUdHl9vA";

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

// ====================================
//   DBバックアップ
// ====================================

// バックアップを保存するディレクトリ
$backupDirectory = "./www/reservation.tisetusyou-pta.org/backup/";

// バックアップするテーブルのリスト
$tables = array("flag_exclude", "flag_point", "flag_reservation", "grade_setting", "patrol_exclude", "patrol_point", "patrol_reservation", "report");

// 各テーブルのデータをエクスポートしてCSVファイルに書き込む
foreach ($tables as $table) {
    // データを取得するためのAPIエンドポイント
    $apiEndpoint = $supabaseUrl . "/rest/v1/" . $table . "?apikey=" . $supabaseKey;

    // データを取得
    $data = file_get_contents($apiEndpoint);

    if ($data === false) {
        echo "Failed to fetch data from Supabase API for table: $table";
        continue; // 次のテーブルへ
    }

    // データをCSV形式に変換
    $rows = json_decode($data, true);

    $csvFilePath = $backupDirectory . $table . ".csv";

    // CSVファイルにデータを書き込む
    $csvFile = fopen($csvFilePath, 'w');
    if ($csvFile === false) {
        echo "Failed to open CSV file for writing for table: $table";
        continue; // 次のテーブルへ
    }

    // CSVヘッダを書き込む
    fputcsv($csvFile, array_keys($rows[0]));

    // データを書き込む
    foreach ($rows as $row) {
        fputcsv($csvFile, $row);
    }

    // CSVファイルを閉じる
    fclose($csvFile);

    echo "Data successfully backed up for table: $table to: $csvFilePath\n";
}
?>
