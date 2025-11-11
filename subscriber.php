<?php
require("phpMQTT.php");
use Bluerhinos\phpMQTT;

// Koneksi ke broker MQTT
$server = "broker.emqx.io";
$port = 1883;
$username = "";
$password = "";
$client_id = "php_subscriber_" . uniqid();

$mqtt = new phpMQTT($server, $port, $client_id);

if(!$mqtt->connect(true, NULL, $username, $password)) {
    exit(1);
}

$mysqli = new mysqli("localhost", "root", "", "cuaca_iot");
if ($mysqli->connect_errno) {
    die("Gagal konek DB: " . $mysqli->connect_error);
}

global $suhu, $humid, $light;
$suhu = $humid = $light = null;

function procMsg($topic, $msg) {
    global $suhu, $humid, $light, $mysqli;

    if ($topic == "sensor/suhu") {
        $suhu = floatval($msg);
    } elseif ($topic == "sensor/humid") {
        $humid = floatval($msg);
    } elseif ($topic == "sensor/light") {
        $light = intval($msg);
    }

    // Jika semua sudah terisi, masukkan ke DB
    if ($suhu !== null && $humid !== null && $light !== null) {
        $query = "INSERT INTO data_sensor (suhu, humidity, lux, timestamp)
                  VALUES ($suhu, $humid, $light, NOW())";
        $mysqli->query($query);

        echo "Data tersimpan: Suhu=$suhu | Humid=$humid | Light=$light\n";

        // Reset variabel
        $suhu = $humid = $light = null;
    }
}

$topics['sensor/suhu'] = ["qos" => 0, "function" => "procMsg"];
$topics['sensor/humid'] = ["qos" => 0, "function" => "procMsg"];
$topics['sensor/light'] = ["qos" => 0, "function" => "procMsg"];

$mqtt->subscribe($topics, 0);

while($mqtt->proc()) {}

$mqtt->close();
?>
