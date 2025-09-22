<?php
/**
 * Copyright 2023 NXP
 * SPDX-License-Identifier: BSD-3-Clause
 */

class RadioController {
    private $socketPath = '/tmp/quantum_unix.sock';
    private $socket = null;
    
    public function __construct() {
        $this->connectToSocket();
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
        $responseBytes = [];
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
        
        
        // Convert header to byte array
        for ($i = 0; $i < strlen($headerBuffer); $i++) {
            $responseBytes[] = ord($headerBuffer[$i]);
        }
        
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
                for ($i = 0; $i < strlen($buffer); $i++) {
                    $responseBytes[] = ord($buffer[$i]);
                }
                $payloadBytesRead += strlen($buffer);
            }
            $this->log_to_file("Payload content: ". bin2hex($payloadBytesRead));
        }
        
        if (empty($responseBytes)) {
            $this->handleSocketError("Failed to read response");
            return json_encode(["error" => "Failed to read response"]);
        }
        
        // Return byte array
        return $responseBytes;
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
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    // Send boot command
                    $output = $this->sendCommand([0x01, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    // Send attach command
                    $output = $this->sendCommand([0x02, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    // Send start scan command
                    $output = $this->sendCommand([0x03, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                        break;
                    }
                    $output['status'] = "OK";
                    break;
                case 'stop':
                    $output = $this->sendCommand([0x00, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                case 'reset':
                    $output = $this->sendCommand([0x00, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                    break;
                case 'start_scan':
                    $output = $this->sendCommand([0x03, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                    break;
                case 'list':
                    $output = $this->sendCommand([0x05, 0x00, 0x00]);

                    break;
                case "stop_scan":
                    $output = $this->sendCommand([0x04, 0x00, 0x00]);
                    if ($ret[3] !== 0x01) {
                        $output['status'] = "Error";
                    else
                        $output['status'] = "OK";
                    break;
                    break;
                default:
                    // TODO: report error
                    echo "Unknow command.";
            }
        } elseif (isset($_GET['tune'])) {
            $stationId = $_GET['tune'];
            $output = $this->sendCommand("tune " . escapeshellarg($stationId) . "\n");
        } else {
            // Default to list stations
            $output = $this->sendCommand("list\n");
        }
        
        // TODO: parse $output

        // TODO: encode response message as json format to ajax
        if (is_string($output) && json_decode($output) === null) {
            echo json_encode([$output]);
        } else {
            echo "output is bytes array.";
            var_dump($output);
        }
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