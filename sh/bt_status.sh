#!/bin/bash
# Copyright 2025 NXP

# three status:
# status1. no paired device
# status2. has paired device but not connected
# status3. device connected

devnumber=$(echo "devices Paired" | bluetoothctl | awk '/Device/ {print $2; exit}')

sleep 1

if [ -z "$devnumber" ]; then
    echo "status_1"
    echo "msg=There is no paired device"
    exit 1
else
    status=$(echo "info $devnumber" | bluetoothctl | awk '/Connected:/ {print $2}')

    if [ "$status" = "yes" ]; then
        echo "status_3"
        echo "device=$devnumber"
        echo "msg=connected"
        exit 0
    else
        echo "status_2"
        echo "device=$devnumber"
        echo "msg=paired but not connected"
        exit 2
    fi
fi
