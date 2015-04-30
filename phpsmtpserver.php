<?php

define("PHP_CRLF", "\r\n");
define('SENDMAIL', '/usr/sbin/sendmail');

include 'config.php';
date_default_timezone_set($config["TIME_ZONE"]);
//multi-thread socket
//pecl pthreads....
class Client extends Thread {

    public function __construct($socket) {
        $this->socket = $socket;
        $this->start();
    }

    public function run() {
        include 'config.php';
        date_default_timezone_set($config["TIME_ZONE"]);
        $conn = $this->socket;
        if ($conn) {
            $socketid = mt_rand(1, 100000);
            $frommime = "";
            $recipients = array();
            $data = "";
            $getData = false;
            $tmpmimefilename = "";
            sendMessage($conn, "220", gethostname() . " SMTP  Remmd");
            while (($buffer = fgets($conn)) !== false) {
                phpwrite($buffer, $socketid);
                $rbuffer = $buffer;
                $buffer = strtolower(trim($buffer));
                if ($buffer == "quit") {
                    sendMessage($conn, "221", gethostname());
                   // fclose($conn);
                    continue;
                }
                if ($buffer == ".") {
                    $getData = false;
                    sendMessage($conn, "100", "250 Ok will send to mail");
                    if ($config["SAVE_TMP_FILE"] == true) {
                        $tmpmimefilename = $config["TMP_DIR"] . DIRECTORY_SEPARATOR;
                        if (!empty($config["TMP_FILE_FORMAT_D"]))
                            $tmpmimefilename .=date($config["TMP_FILE_FORMAT_D"]);
                        if (!empty($config["TMP_FILE_FORMAT_RAND"]))
                            $tmpmimefilename .="." . mt_rand($config["TMP_FILE_FORMAT_RAND"][0], $config["TMP_FILE_FORMAT_RAND"][1]);
                        $filename .= ".eml";
                        file_put_contents($tmpmimefilename, $data);
                    }
                    //phpwrite($data, $socketid);
                   // phpwrite(PHP_CRLF . SENDMAIL . " -f $from " . implode(" ", $recipients) . "<" . $filename, $socketid);
                    //$sendmail = shell_exec(SENDMAIL . " -f $from " . implode(" ", $recipients) . "<" . $filename);
                    continue;
                }
                if ($getData == true) {
                    $data .= $rbuffer;
                }
                if ($buffer == "data") {
                    if (count($recipients) == 0) {
                        sendMessage($conn, "503", " Need RCPT Command");
                        continue;
                    }
                    if (empty($from)) {
                        sendMessage($conn, "503", " Need MAIL FROM Command");
                        continue;
                    }
                    sendMessage($conn, "354");
                    $getData = true;
                    $data = "";
                }
                if (substr($buffer, 0, 4) == "ehlo") {
                    fwrite($conn, "250-" . gethostname() . PHP_CRLF);
                    fwrite($conn, "250-PIPELINING" . PHP_CRLF);
                    fwrite($conn, "250-SIZE 10240000" . PHP_CRLF);
                    fwrite($conn, "250-VRFY" . PHP_CRLF);
                    fwrite($conn, "250-ETRN" . PHP_CRLF);
                    fwrite($conn, "250-ENHANCEDSTATUSCODES" . PHP_CRLF);
                    fwrite($conn, "250 DSN" . PHP_CRLF);
                    continue;
                }
                if (preg_match_all('/^mail from:(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                    $address = (isset($matches[0][2]) ? $matches[0][2] : $matches[0][1]);
                    if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)
                        sendMessage($conn, "501", "invalid mail address " . $address);
                    else {
                        $frommime = $address;
                        sendMessage($conn, "250");
                    }
                    continue;
                }
                if (preg_match_all('/^rcpt to:(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                    $address = (isset($matches[0][2]) ? $matches[0][2] : $matches[0][1]);
                    if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)
                        sendMessage($conn, "501", "invalid mail address " . $address);
                    else {
                        $recipients[] = $address;
                        sendMessage($conn, "250");
                    }
                    continue;
                }
                if ($getData == false) {
                    sendMessage($conn, "502", "invalid command");
                    continue;
                }
            }
            fclose($conn);
            if (!empty($tmpmimefilename) && $config["MAIL_PARSE"])
                include $config["MAIL_PARSE_LIB"];
        }
    }

}

//send message to client on server socket
function sendMessage($conn, $code, $msg = "") {
    global $replyCodes;
    fwrite($conn, sprintf((isset($replyCodes[$code]) ? $replyCodes[$code] : "%s"), $msg) . PHP_CRLF);
}

//Write to log or syslog
function phpwrite($message, $socketid, $priority = LOG_ALERT) {
    global $config;
    if ($config["LOG_2_SYSLOG"] == true)
        syslog($priority, $message);
    else
        print date("c") . ":PID->" . getmypid() . ":SID:$socketid:" . rtrim($message) . PHP_EOL;
}

ini_set("default_socket_timeout", $config["SOCKET_TIMEOUT"]);
$socket = stream_socket_server($config["PROTOCOL"] . "://" . $config["HOST_IP"] . ":" . $config["PORT_NUMBER"], $errno, $errstr);
if (!$socket) {
    phpwrite("$errstr ($errno)");
} else {
    phpwrite("Welcome Simple phpsmptserver");
    while ($conn = stream_socket_accept($socket, -1)) {
        $conns[] = new Client($conn);
    }
    //fclose($socket);
}

