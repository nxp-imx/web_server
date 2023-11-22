<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('lscpu',$output);
    echo json_encode($output);
?>