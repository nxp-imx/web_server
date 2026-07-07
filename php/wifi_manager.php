<?php
/**
* Copyright 2026 NXP
* SPDX-License-Identifier: BSD-3-Clause
*/

header('Content-Type: application/json');
$action = $_GET["action"] ?? "";
/* script path */
$conf_file = "/run/webui/wpa_supplicant.conf";
$create_script  = __DIR__ . '/../sh/create_wifi_conf.sh';
$connect_script = __DIR__ . '/../sh/wifi_connect.sh';
/* ========== connect wifi========== */
if ($action === "connect") {
    $wifi_name = $_GET["wifi_name"] ?? "";
    $wifi_pwd  = $_GET["wifi_pwd"] ?? "";

    if ($wifi_name === "" || $wifi_pwd === "") {
        echo json_encode([
            "status" => "error",
            "msg" => "wifi_name or wifi_pwd is empty"
        ]);
        exit;
    }

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
    exec("ifconfig mlan0 2>&1", $out4, $ret4);
    $result["ifconfig"] = [
        "ret" => $ret4,
        "output" => $out4
    ];

    echo json_encode([
        "status" => ($ret3 === 0 ? "ok" : "error"),
        "result" => $result
    ]);
    exit;
}else if ($action === "disconnect") {
    $result = [];
    /* Step 1: killall wpa_supplicant */
    $out1 = [];
    $ret1 = -1;
    exec("killall wpa_supplicant 2>&1", $out1, $ret1);
    $result["killall_wpa_supplicant"] = [
        "ret" => $ret1,
        "output" => $out1
    ];
    /* Step 2: killall udhcpc */
    $out2 = [];
    $ret2 = -1;
    exec("killall udhcpc 2>&1", $out2, $ret2);
    $result["killall_udhcpc"] = [
        "ret" => $ret2,
        "output" => $out2
    ];
    /* Step 3: ip link set mlan0 down */
    $out3 = [];
    $ret3 = -1;
    exec("ip link set mlan0 down 2>&1", $out3, $ret3);
    $result["ip_link_down"] = [
        "ret" => $ret3,
        "output" => $out3
    ];
    /* Step 4: ip link set mlan0 up */
    $out4 = [];
    $ret4 = -1;
    exec("ip link set mlan0 up 2>&1", $out4, $ret4);
    $result["ip_link_up"] = [
        "ret" => $ret4,
        "output" => $out4
    ];
    /* Step 5: dump ifconfig */
    $out5 = [];
    $ret5 = -1;
    exec("ifconfig mlan0 2>&1", $out5, $ret5);
    $result["ifconfig"] = [
        "ret" => $ret5,
        "output" => $out5
    ];

    echo json_encode([
        "status" => ($ret4 === 0 ? "ok" : "error"),
        "result" => $result
    ]);
    exit;
}else if ($action === "status") {
    $out = [];
    $ret = -1;
    exec('iw mlan0 link 2>&1', $out, $ret);
    /* Get SSID */
    $ssid = "";
    $connected = false;

    foreach ($out as $line) {
        if (strpos($line, "Connected to") !== false) {
            $connected = true;
        }
        if (preg_match('/SSID:\s*(.+)/', $line, $matches)) {
            $ssid = trim($matches[1]);
        }
    }
    
    echo json_encode([
        "status" => "ok",
        "connected" => $connected,
        "ssid" => $ssid
    ]);
    exit;
}else if ($action === "scan") {
    exec("modprobe moal mod_para=nxp/wifi_mod_para.conf 2>&1");
    exec("ifconfig mlan0 up 2>&1");
    $output = [];
    exec('iw dev mlan0 scan | grep SSID', $output);
    echo json_encode($output);
    exit;
}else {
    echo json_encode([
        "status" => "error",
        "msg" => "Invalid action. Use: connect, disconnect, or status"
    ]);
    exit;
}
?>
