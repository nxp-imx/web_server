# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
#set static IP
network_file="/etc/systemd/network/20-wired-static.network"
if [ ! -f network_file  ]; then
    touch $network_file
    echo "[Match]" > $network_file
    echo "Name=eth0" >> $network_file
    echo "[Network]" >> $network_file
    echo "Address=10.192.241.101/24" >> $network_file
    echo "DNS=8.8.8.8" >> $network_file
    echo "Gateway=10.192.241.254" >> $network_file
   
fi

network_file_path="/etc/systemd/network/"
for file in `ls -a $network_file_path`
do
    if [ "${file##*.}" =  "network" ]; then
        firstv=${file:0:1}
        secondv=${file:1:1}
        if echo "$firstv" | grep -q '^[0-9]\+$'; then
            if echo "$secondv" | grep -q '^[0-9]\+$'; then
                var=${file:0:2}
                if [ $var -gt 20 ];then
                  mv $network_file_path$file $network_file_path"bk-"$file
                fi
            fi
        fi
    fi
done
systemctl restart systemd-networkd
sleep 1
wpa_supplicant -d -B -i mlan0 -c /etc/wpa_supplicant/wpa_supplicant.conf -Dnl80211;
sleep 3;
ifconfig eth0 down;
udhcpc -i mlan0;
sleep 5
# get current ip address
ip=`ifconfig mlan0 | grep netmask | tr -s " " | cut -d" " -f3`

#change ip address to *.*.*.101
ipchange=${ip%.*}".101"

ifconfig mlan0 $ipchange
ifconfig eth0 up

