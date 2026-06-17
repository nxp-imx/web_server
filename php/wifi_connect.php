<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
header('Content-Type: application/json');

$wifi_name = $_GET["wifi_name"] ?? "";
$wifi_pwd  = $_GET["wifi_pwd"] ?? "";

if ($wifi_name === "" || $wifi_pwd === "") {
    echo json_encode([
        "status" => "error",
        "msg" => "wifi_name or wifi_pwd is empty"
    ]);
    exit;
}

/*  using script create_wifi_conf.sh */
$conf_file = "/run/webui/wpa_supplicant.conf";

/* ??? PHP ?????????,????? /www/pages/web_server/... */
$create_script  = __DIR__ . '/../sh/create_wifi_conf.sh';
$connect_script = __DIR__ . '/../sh/wifi_connect.sh';

$result = [];

/* Step 1: create conf file */
$out1 = [];
$ret1 = -1;
exec("sh " . escapeshellarg($create_script) . " 2>&1", $out1, $ret1);
$result["create_wifi_conf"] = [
    "ret" => $ret1,
    "output" => $out1,
    "script" => $create_script
];

if ($ret1 !== 0) {
    echo json_encode([
        "status" => "error",
        "step" => "create_wifi_conf",
        "result" => $result
    ]);
    exit;
}

/* Step 2: generate wpa_supplicant config */
$out2 = [];
$ret2 = -1;
$cmd2 = "wpa_passphrase " . escapeshellarg($wifi_name) . " " . escapeshellarg($wifi_pwd) .
        " >> " . escapeshellarg($conf_file) . " 2>&1";
exec($cmd2, $out2, $ret2);
$result["wpa_passphrase"] = [
    "ret" => $ret2,
    "output" => $out2,
    "cmd" => $cmd2
];

if ($ret2 !== 0) {
    echo json_encode([
        "status" => "error",
        "step" => "wpa_passphrase",
        "result" => $result
    ]);
    exit;
}

/* Step 3: connect wifi */
$out3 = [];
$ret3 = -1;
exec("sh " . escapeshellarg($connect_script) . " 2>&1", $out3, $ret3);
$result["wifi_connect"] = [
    "ret" => $ret3,
    "output" => $out3,
    "script" => $connect_script
];

/* Step 4: dump ifconfig */
$out4 = [];
$ret4 = -1;
exec("ifconfig 2>&1", $out4, $ret4);
$result["ifconfig"] = [
    "ret" => $ret4,
    "output" => $out4
];

echo json_encode([
    "status" => ($ret3 === 0 ? "ok" : "error"),
    "result" => $result
]);
exit;
?>
