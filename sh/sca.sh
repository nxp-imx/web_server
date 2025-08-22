#!/bin/bash
function sca_init(){
    killall cat
    stty -F /dev/ttyLP4 115200 cs8 -parenb -cstopb raw -echo -echoe -echok -echoctl -echoke
    echo -e "reset\r\n">/dev/ttyLP4
    sleep 1
    echo -e "factoryreset\r\n">/dev/ttyLP4
}

function sca_reset(){
    killall cat
    stty -F /dev/ttyLP4 115200 cs8 -parenb -cstopb raw -echo -echoe -echok -echoctl -echoke
    echo -e "reset\r\n">/dev/ttyLP4
    sleep 1
    echo -e "reset\r\n">/dev/ttyLP4
}

function distance_get(){
    cat /dev/ttyLP4 &
    sleep 1
    killall cat
}

# ================ [ main ] ================

myself=$0
while [ $# -gt 0 ]
do
	echo -e "[$1]"
	case "$1" in
		init)
            sca_init;;
		get)
            distance_get;;
        reset)
            sca_reset;;
		*) echo -n "Error, Unsupported cmd: [$1]"; exit -1;;
	esac
	shift
done