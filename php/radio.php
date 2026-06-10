<?php
/**
 * Copyright 2025 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */

class RadioController {
    private $socketPath = '/run/quantum_unix.sock';
    private $socket = null;
    
    public function __construct() {
        $this->log_to_file("prepare to connect to ux socket.");
    }
    
    // log
    private function log_to_file($message, $filename = '/tmp/radio_debug.log') {
        $timestamp = date('Y-m-d H:i:s');
        $log_message = "[{$timestamp}] {$message}" . PHP_EOL;
        file_put_contents($filename, $log_message, FILE_APPEND | LOCK_EX);
    }
    
    private function connectToSocket() {
        // Create Unix socket connection
        $this->socket = socket_create(AF_UNIX, SOCK_STREAM, 0);
        if ($this->socket === false) {
            $this->handleSocketError("Unable to create socket");
            return;
        }
        
        // Connect to socket server
        $result = socket_connect($this->socket, $this->socketPath);
        if ($result === false) {
            $this->handleSocketError("Unable to connect to socket server");
            socket_close($this->socket);
            $this->socket = null;
        }
    }
    
    private function handleSocketError($message) {
        $errorCode = socket_last_error();
        $errorMsg = socket_strerror($errorCode);
        $this->log_to_file("Socket Error: " . $message . " - " . $errorMsg);
    }

    private function parse_station_list($station_list_message) {
        // message example: bearer(1) + frequency(8) + quality(1)
        // 01 00 00 00 00 05 37 24 e0 1f 
        // 01 00 00 00 00 05 3e c6 00 1e 
        // 01 00 00 00 00 05 37 24 e0 1e 
        // 01 00 00 00 00 05 37 24 e0 20 
        // 01 00 00 00 00 05 b8 d8 00 1e
        $station_list = [];
        if (strlen($station_list_message) % 10 !== 0) {
            $this->log_to_file("parse station list error, station_list_message: " . bin2hex($station_list_message));
            return $station_list;
        }

        $station_cnt = strlen($station_list_message) / 10;
    
        for ($i = 0; $i < $station_cnt; $i++) {
            $offset = $i * 10;
        
            // Get Bearer
            $bearer = ord($station_list_message[$offset]);
        
            // Get ferquency
            $frequency_bytes = substr($station_list_message, $offset + 1, 8);
            $frequency = unpack('J', $frequency_bytes)[1];
        
            // Get quality
            $quality = ord($station_list_message[$offset + 9]);
        
            $station_list[] = [
                'bearer' => $bearer,
                'frequency' => $frequency,
                'quality' => $quality
            ];
        }
        return $station_list;
    }
    
    private function sendCommand($command) {
        // Check socket connection status
        if ($this->socket === null) {
            $this->connectToSocket();
            if ($this->socket === null) {
                return json_encode(["error" => "Not connected to socket server"]);
            }
        }
        
        // Convert command to byte stream - now supports direct byte array passing
        $byteStream = '';
        if (is_array($command)) {
            // If it's an array, assume it's a byte array
            foreach ($command as $byte) {
                $byteStream .= chr($byte);
            }
        } else {
            // Original logic: process string commands
            $commandParts = explode(' ', $command);
            foreach ($commandParts as $part) {
                if (ctype_xdigit($part) && strlen($part) == 2) {
                    $byteStream .= chr(hexdec($part));
                }
            }
            
            // If no valid hex data, use the original command string
            if (empty($byteStream)) {
                $byteStream = $command;
            }
        }
        
        // Send command to socket server
        $result = socket_write($this->socket, $byteStream, strlen($byteStream));
        if ($result === false) {
            $this->handleSocketError("Failed to send command: " . (is_array($command) ? implode(' ', $command) : $command));
            return json_encode(["error" => "Failed to send command"]);
        }
        
        // Read response: first read 3-byte header, then read payload based on length field
        $responseBytes = '';
        // First read 3 bytes as header
        $headerBuffer = '';
        $headerBytesRead = 0;
        while ($headerBytesRead < 3) {
            $buffer = socket_read($this->socket, 3 - $headerBytesRead);
            if ($buffer === false || strlen($buffer) === 0) {
                break;
            }
            $headerBuffer .= $buffer;
            $headerBytesRead += strlen($buffer);
        }
        
        // Debug: Check header length and content
        $this->log_to_file("Header buffer length: " . strlen($headerBuffer));
        $this->log_to_file("Header buffer content: " . bin2hex($headerBuffer));
        
        
        $responseBytes .= $headerBuffer;
        
        // Check if we have a complete header
        if (strlen($headerBuffer) == 3) {
            // Extract length from first two bytes of header
            $length = (ord($headerBuffer[1]) << 8) | ord($headerBuffer[2]);
            
            // Debug: Check parsed length
            $this->log_to_file("Payload length: " . $length);
            
            // Read the payload based on length
            $payloadBytesRead = 0;
            while ($payloadBytesRead < $length) {
                $buffer = socket_read($this->socket, $length - $payloadBytesRead);
                if ($buffer === false || strlen($buffer) === 0) {
                    $this->log_to_file("Socket read failed or connection closed. Read $payloadBytesRead of $length bytes.");
                    break;
                }

                $responseBytes .= $buffer;
                $payloadBytesRead += strlen($buffer);
            }
            $this->log_to_file("response content: ". bin2hex($responseBytes));
        }
        $this->log_to_file("read socket done.");
        // Return byte array
        return $responseBytes;
    }

    private function stop_audio_player() {
        if (file_exists('/run/gst_launch.pid')) {
            exec("kill -9 -$(cat /run/gst_launch.pid) 2>/dev/null");
            unlink('/run/gst_launch.pid');
        }
    }
    
    public function handleRequest() {
        $output = [];
        
        if (isset($_GET['action'])) {
            $action = $_GET['action'];
            switch ($action) {
                case 'debug':
                    $this->log_to_file("123123");
                    echo "log to file";
                    break;
                case 'start':
                    // Send reset command
                    $ret = $this->sendCommand([0x00, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    // Send boot command
                    $ret = $this->sendCommand([0x01, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    // Send attach command
                    $ret = $this->sendCommand([0x02, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    // Send start scan command
                    $ret = $this->sendCommand([0x03, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    $output['status'] = "OK";
                    break;
                case 'stop':
                    $this->stop_audio_player();
                    $output['status'] = "OK";
                    break;
                case 'reset':
                    $this->stop_audio_player();
                    $ret = $this->sendCommand([0x00, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) 
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                case 'start_scan':
                    $ret = $this->sendCommand([0x03, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) 
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                case 'list':
                    $ret = $this->sendCommand([0x05, 0x00, 0x00]);
                    if (strlen($ret) === 4) {
                        $output['status'] = "Error";
                    } else {
                        $station_message = substr($ret, 3);
                        $output['station_list'] = $this->parse_station_list($station_message);
                        $output['status'] = "OK";
                    }
                    break;
                case "stop_scan":
                    $ret = $this->sendCommand([0x04, 0x00, 0x00]);
                    if (ord($ret[3]) !== 0x01) 
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                default:
                    $this->log_to_file("Unkonw action type: ". $action);
            }
        } elseif (isset($_GET['tune'])) {
            $stationId = $_GET['tune'];
            $intnum = intval($stationId);
            $cmd_array = [0x06, 0x00, 0x08];
            for ($i = 7; $i >= 0; $i--) {
                $cmd_array[] = ($intnum >> ($i * 8)) & 0xFF;
            }
            $ret = $this->sendCommand($cmd_array);
            if (ord($ret[3]) !== 0x01) 
                $output['status'] = "Error";
            else {
                $output['status'] = "OK";
                // Run audio playback command in background after setting status to OK
                if (!file_exists('/run/gst_launch.pid'))
                    exec("nohup bash /www/pages/web_server/sh/open_audio_player.sh > /run/audio.log 2>&1 &");
            }
        } else {
            $this->log_to_file("Unknow request type");
        }
        
        // Encode response message as json format to ajax
        echo json_encode($output);
    }
    
    public function __destruct() {
        // Keep connection open unless explicitly closed or object destroyed
        if ($this->socket !== null) {
            socket_close($this->socket);
        }
    }
}

// Process request
$radioController = new RadioController();
$radioController->handleRequest();
?>