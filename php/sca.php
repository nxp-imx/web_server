<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('sh /www/pages/web_server/sh/sca.sh get_one',$output);
    echo json_encode($output);
?>