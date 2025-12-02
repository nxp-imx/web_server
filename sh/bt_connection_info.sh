# SPDX-License-Identifier: BSD-3-Clause
# Copyright 2025 NXP

devnumber=$(bluetoothctl devices Paired | awk -F ' ' '{print $2}')
bluetoothctl info $devnumber | head -n 11