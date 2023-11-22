<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec("modprobe moal mod_para=nxp/wifi_mod_para.conf;");
    exec("connmanctl enable wifi;");
    exec("connmanctl scan wifi;");
    exec('connmanctl services;', $output);
    echo json_encode($output);
?>