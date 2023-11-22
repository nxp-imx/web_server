<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    $wifi_name=$_GET["wifi_name"];
    $wifi_pwd=$_GET["wifi_pwd"];
    exec("sh /www/pages/web_server/sh/create_wifi_conf.sh");
    exec("wpa_passphrase ".$wifi_name." ".$wifi_pwd." >> /etc/wpa_supplicant/wpa_supplicant.conf");
    exec("sh /www/pages/web_server/sh/wifi_connect.sh");
?>