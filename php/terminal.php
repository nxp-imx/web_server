<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */
session_start();

$command = $_GET["command"];

// Allow explicit reset via ?reset=1
if (!empty($_GET["reset"])) {
    $_SESSION["cwd"] = "/root";
}

// Initialize working directory in session if not set, or if stored path no longer valid
if (empty($_SESSION["cwd"]) || !is_dir($_SESSION["cwd"])) {
    $_SESSION["cwd"] = "/root";
}

$cwd = $_SESSION["cwd"];

// Handle 'cd' command specially to persist directory changes
if (preg_match('/^\s*cd\s*(.*?)\s*$/', $command, $matches)) {
    $target = $matches[1];
    if ($target === '' || $target === '~') {
        $target = getenv("HOME") ?: "/root";
    }
    // Resolve relative paths against current cwd
    if (substr($target, 0, 1) !== '/') {
        $target = $cwd . '/' . $target;
    }
    $realpath = realpath($target);
    if ($realpath === false || !is_dir($realpath)) {
        echo json_encode(["output" => ["cd: " . $matches[1] . ": No such file or directory"], "cwd" => $cwd]);
    } else {
        $_SESSION["cwd"] = $realpath;
        echo json_encode(["output" => [], "cwd" => $realpath]);
    }
    exit;
}

// Handle 'clear' command - return a special signal instead of ANSI escape codes
if (preg_match('/^\s*clear\s*$/', $command)) {
    echo json_encode(["output" => [], "cwd" => $cwd, "clear" => true]);
    exit;
}

// Replace interactive commands with non-interactive equivalents
$command = preg_replace('/^\s*top\s*$/', 'top -bn1', $command);

// Run the command inside the current working directory
exec("cd " . escapeshellarg($cwd) . " && " . $command . " 2>&1", $output, $return_code);

// For commands that succeed with no output (touch, mkdir, rm, etc.)
if (empty($output)) {
    if ($return_code === 0) {
        $output[] = "(command executed successfully, no output)";
    } else {
        $output[] = "(command failed with exit code " . $return_code . ")";
    }
}

echo json_encode(["output" => $output, "cwd" => $cwd]);
?>
