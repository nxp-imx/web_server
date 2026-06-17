<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */

header('Content-Type: application/json');

$output = [];
$return_var = 0;

exec('echo "show" | /usr/bin/bluetoothctl 2>&1', $output, $return_var);

echo json_encode([
    'status' => ($return_var === 0) ? 'ok' : 'error',
    'return_code' => $return_var,
    'output' => $output
]);
?>