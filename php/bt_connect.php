<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    $bt_id=$_GET["bt_id"];  
    exec('expect ../sh/bt_connect.exp '.$bt_id); //Configure the Bluetooth module on OrangeBox
?>