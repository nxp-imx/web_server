<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 *
 * Bluetooth Manager API
 * Unified entry point replacing: bluetooth.php, bt_connect.php,
 * bt_connection_info.php, bt_show.php
 *
 * Usage:
 *   ?action=scan             - Init BT and scan for nearby devices
 *   ?action=connect&bt_id=XX:XX:XX:XX:XX:XX  - Connect to a device
 *   ?action=connection_info  - Get current connection info
 *   ?action=show             - Show local BT adapter info
 */

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {

    // -- bluetooth.php --------------------------------------------------
    case 'scan':
        exec('sh /www/pages/web_server/sh/bt_init.sh');
        exec('bluetoothctl power on');
        exec('bluetoothctl discoverable on');
        exec('bluetoothctl pairable on');
        exec('bluetoothctl default-agent');
        exec('bluetoothctl agent on');
        $output = [];
        exec('sh /www/pages/web_server/sh/scan_bt_devices.sh', $output);
        echo json_encode($output);
        break;

    // -- bt_connect.php -------------------------------------------------
    case 'connect':
        if (!isset($_GET['bt_id'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'bt_id parameter is required']);
            break;
        }
        $bt_id = $_GET['bt_id'];
        exec('expect ../sh/bt_connect.exp ' . $bt_id);
        echo json_encode(['status' => 'ok']);
        break;

    // -- bt_connection_info.php -----------------------------------------
    case 'connection_info':
        $output = [];
        exec('sh ../sh/bt_connection_info.sh', $output);
        echo json_encode($output);
        break;

    // -- bt_show.php ----------------------------------------------------
    case 'show':
        $output = [];
        $return_var = 0;
        exec('echo "show" | /usr/bin/bluetoothctl 2>&1', $output, $return_var);
        echo json_encode([
            'status' => ($return_var === 0) ? 'ok' : 'error',
            'return_code' => $return_var,
            'output' => $output
        ]);
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => 'Unknown or missing action. Valid actions: scan, connect, connection_info, show'
        ]);
        break;
}
?>
