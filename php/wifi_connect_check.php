<?php
/**
 * Copyright 2026 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('iw mlan0 link',$output);
    echo json_encode($output);
?>