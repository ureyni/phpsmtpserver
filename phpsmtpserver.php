<?php

define("PHP_CRLF", "\r\n");
define('SENDMAIL','/usr/sbin/sendmail');
include 'config.php';

$replyCodes = array(
    "500" => "500 Syntax error, command unrecognized %s",
    "501" => "501 Syntax error in parameters or arguments %s",
    "502" => "502 Command not implemented %s",
    "503" => "503 Bad sequence of commands %s",
    "504" => "504 Command parameter not implemented %s",
    "211" => "211 System status, or system help reply %s",
    "214" => "214 Help message  %s",
    "220" => "220 %s Service ready",
    "221" => "221 %s Service closing transmission channel",
    "421" => "421 %s Service not available,closing transmission channel",
    "250" => "250 Requested mail action okay, completed",
    "251" => "251 User not local; will forward to <forward-path>",
    "450" => "450 Requested mail action not taken: mailbox unavailable",
    "550" => "550 Requested action not taken: mailbox unavailable",
    "451" => "451 Requested action aborted: error in processing",
    "551" => "551 User not local; please try <forward-path>",
    "452" => "452 Requested action not taken: insufficient system storage",
    "552" => "552 Requested mail action aborted: exceeded storage allocation",
    "553" => "553 Requested action not taken: mailbox name not allowed",
    "354" => "354 Start mail input; end with <CRLF>.<CRLF>",
    "554" => "554 Transaction failed"
);

function sendMessage($conn, $code, $msg = "") {
    global $replyCodes;
    fwrite($conn, sprintf((isset($replyCodes[$code]) ? $replyCodes[$code] : "%s"), $msg) . PHP_CRLF);
}

$socket = stream_socket_server("tcp://0.0.0.0:8000", $errno, $errstr);
$from = "";
$to = array();
$data = "";
$getData = false;
if (!$socket) {
    echo "$errstr ($errno)<br />\n";
} else {
    stream_set_timeout($socket, $config["SOCKET_TIMEOUT"],0);
    while ($conn = stream_socket_accept($socket)) {
        sendMessage($conn, "220", gethostname() . " SMTP  KEPHS");
        while (($buffer = fgets($conn)) !== false) {
            echo $buffer;
            $rbuffer = $buffer;
            $buffer = strtolower(trim($buffer));
            if ($buffer == "quit") {
                sendMessage($conn, "221", gethostname());
                fclose($conn);
                continue;
            }
            if ($buffer == ".") {
                $getData = false;
                sendMessage($conn, "100", "250 Ok will send to mail");
                if ($config["SAVE_TMP_FILE"] == true) {
                    $filename = $config["TMP_DIR"] . DIRECTORY_SEPARATOR;
                    if (!empty($config["TMP_FILE_FORMAT_D"]))
                        $filename .=date($config["TMP_FILE_FORMAT_D"]);
                    if (!empty($config["TMP_FILE_FORMAT_RAND"]))
                        $filename .="." . mt_rand($config["TMP_FILE_FORMAT_RAND"][0], $config["TMP_FILE_FORMAT_RAND"][1]);
                    $filename .= ".eml";
                    file_put_contents($filename, $data);
                }
                print $data;
                print PHP_CRLF.SENDMAIL . " -f $from ".implode(" ", $to)."<" . $filename;
                $sendmail = shell_exec(SENDMAIL . " -f $from ".implode(" ", $to)."<" . $filename);
                continue;
            }
            if ($getData == true) {
                $data .= $rbuffer;
            }
            if ($buffer == "data") {
                if (count($to) == 0) {
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
                fwrite($conn, "250-8BITMIME" . PHP_CRLF);
                fwrite($conn, "250 DSN" . PHP_CRLF);
                continue;
            }
            if (preg_match_all('/^mail from:(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                $address =  (isset($matches[0][2])?$matches[0][2]:$matches[0][1]);
                if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)
                    sendMessage($conn, "501", "invalid mail address " . $address);
                else {
                    $from = $address;
                    sendMessage($conn, "250");
                }
                continue;
            }
            if (preg_match_all('/^rcpt to:(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                $address =  (isset($matches[0][2])?$matches[0][2]:$matches[0][1]);
                if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)
                    sendMessage($conn, "501", "invalid mail address " . $address);
                else {
                    $to[] = $address;
                    sendMessage($conn, "250");
                }
                continue;
            }
            if ($getData == false) {
                sendMessage($conn, "502", "invalid command");
                continue;
            }
        }
    }
    fclose($socket);
}

