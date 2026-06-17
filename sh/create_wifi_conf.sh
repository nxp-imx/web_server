#!/bin/sh
# Copyright 2023 NXP
# SPDX-License-Identifier: BSD-3-Clause
set -e

CONF_DIR="/run/webui"
CONF_FILE="${CONF_DIR}/wpa_supplicant.conf"

rm -f "${CONF_FILE}"
touch "${CONF_FILE}"

exit 0
