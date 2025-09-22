#!/bin/bash

# Radio simulation script
# This script simulates radio functionality for the OrangeBox Web UI

function radio_start() {
    echo "Starting radio..."
    # Create a status file to indicate radio is running
    echo "running" > /tmp/radio_status
    echo "Radio started"
}

function radio_stop() {
    echo "Stopping radio..."
    # Update status file to indicate radio is stopped
    echo "stopped" > /tmp/radio_status
    # Clear current station
    rm -f /tmp/current_station
    echo "Radio stopped"
}

function radio_reset() {
    echo "Resetting radio..."
    # Reset radio to initial state
    echo "stopped" > /tmp/radio_status
    rm -f /tmp/current_station
    echo "Radio reset"
}

function radio_list() {
    echo "Retrieving station list..."
    # Return a list of available stations
    echo "NXP FM Radio - 98.5 MHz"
    echo "Quantum Hits - 102.7 MHz"
    echo "Classic Rock - 105.3 MHz"
    echo "News Network - 108.9 MHz"
    echo "Jazz Lounge - 88.1 MHz"
    echo "Electronic Waves - 91.2 MHz"
    echo "Country Roads - 95.6 MHz"
}

function radio_tune() {
    local station_id=$1
    local stations=("NXP FM Radio - 98.5 MHz" "Quantum Hits - 102.7 MHz" "Classic Rock - 105.3 MHz" "News Network - 108.9 MHz" "Jazz Lounge - 88.1 MHz" "Electronic Waves - 91.2 MHz" "Country Roads - 95.6 MHz")
    
    if [[ $station_id -ge 1 && $station_id -le ${#stations[@]} ]]; then
        local station="${stations[$((station_id-1))]}"
        echo "$station" > /tmp/current_station
        echo "Tuned to station: $station"
    else
        echo "Invalid station ID: $station_id"
    fi
}

function radio_status() {
    if [[ -f /tmp/radio_status ]]; then
        cat /tmp/radio_status
    else
        echo "stopped"
    fi
}

# ================ [ main ] ================

myself=$0
while [ $# -gt 0 ]
do
    echo -e "[$1]"
    case "$1" in
        start)
            radio_start;;
        stop)
            radio_stop;;
        reset)
            radio_reset;;
        list)
            radio_list;;
        tune)
            shift
            radio_tune $1;;
        status)
            radio_status;;
        *) 
            echo -n "Error, Unsupported cmd: [$1]"; 
            exit -1;;
    esac
    shift
done