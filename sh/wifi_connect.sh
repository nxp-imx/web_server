# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
wpa_supplicant -d -B -i mlan0 -c /etc/wpa_supplicant/wpa_supplicant.conf -Dnl80211;
sleep 3;
udhcpc -i mlan0;