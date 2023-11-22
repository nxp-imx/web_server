<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
    exec('cat /sys/devices/system/cpu/*/cpufreq/cpuinfo_cur_freq',$output);
    echo json_encode($output);
?>