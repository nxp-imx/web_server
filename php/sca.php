<?php
/**
 * SPDX-License-Identifier: BSD-3-Clause
 * Copyright 2025 NXP
 */
    exec('sh /www/pages/web_server/sh/sca.sh get_one',$output);
    echo json_encode($output);
?>