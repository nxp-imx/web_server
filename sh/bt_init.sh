# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
modprobe moal mod_para=nxp/wifi_mod_para.conf
#hciattach /dev/ttyLP1 any -s 3000000 3000000 flow
sleep 1
modprobe btnxpuart
sleep 1
hciconfig hci0 up
hciconfig hci0 piscan
hciconfig hci0 noencrypt
hcitool dev