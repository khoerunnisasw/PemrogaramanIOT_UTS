<?php

header('Content-Type: application/json');

// koneksi database
$mysqli = new mysqli("localhost", "root", "", "cuaca_iot");

// jika koneksi error
if ($mysqli->connect_errno) {
    echo json_encode(["error" => "Gagal konek database"]);
    exit();
}

// ✅ SUHU MAX
$result = $mysqli->query("SELECT MAX(suhu) AS suhumax FROM data_sensor");
$suhuMax = $result->fetch_assoc()['suhumax'];

$result = $mysqli->query("SELECT MIN(suhu) AS suhumin FROM data_sensor");
$suhuMin = $result->fetch_assoc()['suhumin'];

$result = $mysqli->query("SELECT AVG(suhu) AS suhurata FROM data_sensor");
$suhuRata = round($result->fetch_assoc()['suhurata'], 2);

$result = $mysqli->query("SELECT MAX(humidity) AS humidmax FROM data_sensor");
$humidMax = $result->fetch_assoc()['humidmax'];

$query = "
    SELECT 
        id AS idx, 
        suhu AS suhun, 
        humidity AS humid, 
        lux AS kecerahan, 
        timestamp 
    FROM data_sensor
    WHERE suhu = $suhuMax AND humidity = $humidMax
";
$result = $mysqli->query($query);

$nilaiSuhuHumidMax = [];
while ($row = $result->fetch_assoc()) {
    $nilaiSuhuHumidMax[] = $row;
}

$query = "
    SELECT DISTINCT 
        CONCAT(MONTH(timestamp), '-', YEAR(timestamp)) AS month_year
    FROM data_sensor
    WHERE suhu = $suhuMax AND humidity = $humidMax
";
$result = $mysqli->query($query);

$monthYear = [];
while ($row = $result->fetch_assoc()) {
    $monthYear[] = $row['month_year'];
}

$output = [
    "suhu_max" => $suhuMax,
    "suhu_min" => $suhuMin,
    "suhu_rata2" => $suhuRata,
    "nilai_suhu_max_humid_max" => $nilaiSuhuHumidMax,
    "month_year_max" => $monthYear
];

echo json_encode($output, JSON_PRETTY_PRINT);
