# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
devnumber=$(bluetoothctl devices Paired | awk -F ' ' '{print $2}') 
devnumber=$(bluetoothctl devices Paired | awk -F ' ' '{print $2}') 

bluetoothctl info $devnumber | head -n 11

   