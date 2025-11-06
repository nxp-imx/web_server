<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec("modprobe moal mod_para=nxp/wifi_mod_para.conf;");
    exec("ifconfig mlan0 up;");
    exec('iw dev mlan0 scan | grep SSID', $output);
    echo json_encode($output);
?>