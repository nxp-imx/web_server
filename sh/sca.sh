#!/bin/bash
function sca_init(){
    killall cat
    stty -F /dev/ttyLP4 115200 cs8 -parenb -cstopb raw -echo -echoe -echok -echoctl -echoke
    echo -e "reset\r\n">/dev/ttyLP4
    sleep 1
    echo -e "factoryreset\r\n">/dev/ttyLP4
}

function sca_init_one(){
    killall cat
    stty -F /dev/ttyLP4 115200 cs8 -parenb -cstopb raw -echo -echoe -echok -echoctl -echoke
    echo -e "sd op\r\n">/dev/ttyLP4
    sleep 10
}

function sca_reset(){
    killall cat
    stty -F /dev/ttyLP4 115200 cs8 -parenb -cstopb raw -echo -echoe -echok -echoctl -echoke
    echo -e "reset\r\n">/dev/ttyLP4
}

function distance_get(){
    cat /dev/ttyLP4 &
    sleep 1
    killall cat
}


function distance_get_one(){
    cat /dev/ttyLP4 &
    echo -e "tdm 0\r\n">/dev/ttyLP4
    sleep 3
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
        init_one)
            sca_init_one;;
		get_one)
            distance_get_one;;
        reset)
            sca_reset;;
		*) echo -n "Error, Unsupported cmd: [$1]"; exit -1;;
	esac
	shift
done