<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('sh /www/pages/web_server/sh/bt_init.sh'); //Configure the Bluetooth module on OrangeBox
    // exec('bluetoothctl show',$output);   //Show the infomation of Bluetooth on OrangeBox
    exec('bluetoothctl power on');  //Power on the Bluetooth module
    exec('bluetoothctl discoverable on');   //Ensure the OrangeBox could be found by other devices
    exec('bluetoothctl pairable on');
    exec('bluetoothctl default-agent');
    exec('bluetoothctl agent on'); 
    exec('bluetoothctl --timeout 10 scan on');   //scan devices for 10 seconds and stop.

    exec('bluetoothctl devices',$output);   //Show the devices scanned and trans them to Website
    echo json_encode($output);
?>