#!/bin/bash
export XDG_RUNTIME_DIR=/run/user/0/
export DBUS_SESSION_BUS_ADDRESS=unix:path=/run/user/0/bus
# New pipe process group
(
    exec setsid bash -c '
        arecord -D hw:qtmradiocard,0 -f S32_LE -r 48000 -c 1 -t wav | \
        gst-launch-1.0 fdsrc ! wavparse ! volume volume=0.1 ! autoaudiosink
    '
) &

PIPE_PID=$!

sleep 0.5

# Get PGID
PIPE_PGID=$(ps -o pgid= -p "$PIPE_PID" | tr -d ' ')

# Write to file for later management
echo "$PIPE_PGID" > /run/gst_launch.pid