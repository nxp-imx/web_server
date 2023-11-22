<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('cat /proc/meminfo',$output);
    echo json_encode($output);
?>