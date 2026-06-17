#!/bin/bash
# SPDX-License-Identifier: BSD-3-Clause
# Copyright 2025 NXP

devnumber=$(echo "devices Paired" | bluetoothctl | awk '/Device/ {print $2; exit}')

if [ -z "$devnumber" ]; then
    echo "No paired device"
    exit 1
fi

output=$(printf "info %s\nquit\n" "$devnumber" | bluetoothctl 2>/dev/null)

echo "$output" | sed -n '/^Device /,$p' | head -n 11
exit 0
