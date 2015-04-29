<?php

$config["PORT_NUMBER"] = 8000;
$config["HOST_IP"] = "0.0.0.0"; 
$config["PROTOCOL"] = "tcp";        
$config["SOCKET_TIMEOUT"] = 3600;
$config["SAVE_TMP_FILE"] = true;
$config["TMP_DIR"] = "/data/maildata01";
$config["TMP_FILE_FORMAT_D"] = "dmYHis" ;
$config["TMP_FILE_FORMAT_RAND"] = array(1000000, 9000000);
$config["LOG_2_SYSLOG"] = false;
$config["TIME_ZONE"] = "Europe/Istanbul";


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

?>