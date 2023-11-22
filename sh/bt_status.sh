# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
#three status：1.no paired device；2.has paired device but not connect；3.device connected
devnumber=$(bluetoothctl devices Paired | awk -F ' ' '{print $2}') 

if [ ! -n "$devnumber" ]; then
    echo "status 1"
    echo "There is no paired device"
else
    status=$(bluetoothctl info $devnumber | grep Connected | awk -F ' ' '{print $2}')
    if [[ $status = "no" ]] ; then
        echo "status 2"
        echo "paired but not connected"
    else
        echo "status 3"
        echo "connected"
    fi
fi