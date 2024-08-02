<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('sh /www/pages/web_server/sh/gnss.sh get',$output);
    echo json_encode($output);
?>