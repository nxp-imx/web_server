#!/bin/bash


function gnss_init(){
    cd `dirname $0` 
    dev=/dev/ttyLP3
    ANT_EN="\xb5\x62\x06\x8a\x1d\x00\x00\x01\x00\x00\x2e\x00\xa3\x10\x01\x2f\x00\xa3\x10\x01\x31\x00\xa3\x10\x01\x33\x00\xa3\x10\x01\x35\x00\xa3\x10\x01\x28\x0f"
    stty -F $dev 38400 raw -echo
    echo -ne $ANT_EN > $dev
    stty -F /dev/ttyLP3 38400
}

function gnss_get(){
    cat /dev/ttyLP3 | grep GNGGA &
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
            gnss_init;;
		get)
            gnss_get;;
		*) echo -n "Error, Unsupported cmd: [$1]"; exit -1;;
	esac
	shift
done