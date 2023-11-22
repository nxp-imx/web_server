#!/bin/bash
# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
  
# Get the first line with aggregate of all CPUs
cpu_now=($(sed -n '2p' /proc/stat))
# Get all columns but skip the first (which is the "cpu" string)
cpu_sum="${cpu_now[@]:1}"
# Replace the column seperator (space) with +
cpu_sum=$((${cpu_sum// /+}))
# Get the delta between two reads
cpu_delta=$((cpu_sum - cpu_last_sum))
echo $cpu_delta
# Get the idle time Delta
cpu_idle=$((cpu_now[4]- cpu_last[4]))
# Calc time spent working
cpu_used=$((cpu_delta - cpu_idle))
# Calc percentage
cpu_usage=$((100 * cpu_used / cpu_delta))

# Keep this as last for our next read
cpu_last=("${cpu_now[@]}")
cpu_last_sum=$cpu_sum

echo "$cpu_usage"
