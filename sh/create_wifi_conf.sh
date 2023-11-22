# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
if [ ! -d "/etc/wpa_supplicant" ]; then
  mkdir /etc/wpa_supplicant
fi

if [ -f "/etc/wpa_supplicant/wpa_supplicant.conf" ]; then
  rm /etc/wpa_supplicant/wpa_supplicant.conf
fi

touch /etc/wpa_supplicant/wpa_supplicant.conf

