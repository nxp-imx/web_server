<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('sh ../sh/bt_connection_info.sh',$output);
    echo json_encode($output);
?>