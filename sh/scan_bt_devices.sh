#!/bin/bash

SCAN_TIME=5

bluetoothctl power on >/dev/null 2>&1

OUTPUT=$( (
    echo "scan on"
    sleep ${SCAN_TIME}
    echo "scan off"
) | bluetoothctl )

echo "$OUTPUT" | grep "Device"
