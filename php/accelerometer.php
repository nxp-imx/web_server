<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('sh ../sh/accelerometer.sh',$output);
    echo json_encode($output);
?>