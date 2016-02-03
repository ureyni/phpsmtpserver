<?php

define("PHP_CRLF", "\r\n");
define('SENDMAIL', '/usr/sbin/sendmail -oi');

include 'config_smtp.php';
date_default_timezone_set($config["TIME_ZONE"]);
$socket_counter = 0;

function smtp_shutdown() {
    //global $hatabildirimlistesi;
    $error = error_get_last();
    if ($error['type'] === E_ERROR || $error['type'] == E_USER_ERROR) {
        $debug = debug_backtrace(true);
        $message = "SMTP Fatal Error From Server : Error  : " . var_export($error, true) . " Debug:" . var_export($debug, true) . "</br>İnfo Server:->" . SERVERNAME . " IP->" . SERVERNAME;
        print PHP_EOL . "FATAL : " . Date("c") . ":" . (defined("GUID") ? GUID : '...') . ":" . $message;
//        $ret = errorSend("GENEL", $message);
    }
    print PHP_EOL . "ERROR : " . Date("c") . ":" . (defined("GUID") ? GUID : '...') . ":" . var_export($error, true);
}

register_shutdown_function('smtp_shutdown');

//multi-thread socket
//pecl pthreads....
file_put_contents(preg_replace('/\.php$/', '', __FILE__) . ".pid", PHP_EOL . date('c') . "-->" . getmypid(), FILE_APPEND);

class Client extends Thread {

    public function __construct($socket) {
        $this->socket = $socket;
        $this->start();
    }

    public function run() {
        register_shutdown_function('smtp_shutdown');
        include 'config_smtp.php';
        $int = gc_collect_cycles();

        file_put_contents(preg_replace('/\.php$/', '', __FILE__) . ".pid", PHP_EOL . date('c') . "-->Creator Id-> " . $this->getCreatorId() . ":Thread Id-> " . Thread::getCurrentThreadId(), FILE_APPEND);

        if ($config["LOG_MEM_INFO"] == true)
            phpwrite(PHP_EOL . Date("c") . ":" . (defined("GUID") ? GUID : '...') . memory_get_peak_usage(true) . ":$socket_counter : Start Memory info : " . $int . ":" . memory_get_usage() . ":" . memory_get_usage(true));

        if ($config["MAIL_PARSE"] == true)
            include $config["MAIL_PARSE_LIB"];
        date_default_timezone_set($config["TIME_ZONE"]);
        $conn = $this->socket;
        if ($conn) {
            $socketid = mt_rand(1, 100000);
            $frommime = "";
            $recipients = array();
            $data = "";
            $getData = false;
            $tmpmimefilename = "";
            $output = array();
            sendMessage($conn, "220", gethostname() . " SMTP  Remmd");
            while (($buffer = fgets($conn)) !== false) {
                if ($getData == false)
                    phpwrite($buffer, $socketid);
                $rbuffer = $buffer;
                $buffer = strtolower(trim($buffer));
                if ($buffer == "quit") {
                    sendMessage($conn, "221", gethostname());
                    //fclose($conn);
                    stream_socket_shutdown($conn, STREAM_SHUT_WR);
                    continue;
                }
                if ($buffer == ".") {
                    $getData = false;
                    sendMessage($conn, "100", "250 Ok will send to mail");
                    if ($config["SAVE_TMP_FILE"] == true) {
                        $tmpmimefilename = $config["TMP_DIR"] . DIRECTORY_SEPARATOR;
                        if (!empty($config["TMP_FILE_FORMAT_D"]))
                            $tmpmimefilename .= date($config["TMP_FILE_FORMAT_D"]);
                        if (!empty($config["TMP_FILE_FORMAT_RAND"]))
                            $tmpmimefilename .= "." . mt_rand($config["TMP_FILE_FORMAT_RAND"][0], $config["TMP_FILE_FORMAT_RAND"][1]);
                        if (!file_exists(dirname($tmpmimefilename)))
                            mkdir(dirname($tmpmimefilename), 0777, true);

                        $tmpmimefilename .= ".eml";
                        file_put_contents($tmpmimefilename, $data);
                    }
                    //phpwrite($data, $socketid);
                    // phpwrite(PHP_CRLF . SENDMAIL . " -f $frommime " . implode(" ", $recipients) . "<" . $tmpmimefilename, $socketid);
                    //$sendmail = shell_exec(SENDMAIL . " -f $frommime " . implode(" ", $recipients) . "<" . $tmpmimefilename);
                    continue;
                }
                if ($getData == true) {
                    if (substr($rbuffer, 0, 2) == '..' && strlen($rbuffer) > 2)
                        $rbuffer = substr($rbuffer, 1);
                    $data .= $rbuffer;
                }
                if ($buffer == "data") {
                    if (count($recipients) == 0) {
                        sendMessage($conn, "503", " Need RCPT Command");
                        continue;
                    }
                    if (empty($frommime)) {
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
                if (preg_match_all('/^mail from:(\s|)(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                    $address = (isset($matches[0][3]) ? $matches[0][3] : $matches[0][2]);
                    if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)
                        sendMessage($conn, "501", "invalid mail address " . $address);
                    else {
                        $frommime = $address;
                        sendMessage($conn, "250");
                    }
                    continue;
                }
                if (preg_match_all('/^rcpt to:(\s|)(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                    $address = (isset($matches[0][3]) ? $matches[0][3] : $matches[0][2]);
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
            // fclose($conn);
            if ($frommime == $config["SYSTEM_SERVICE_ADDR"]) {
                $output = array();
                $retval = 0;
                $sendmail = exec(SENDMAIL . " -f $frommime " . implode(" ", $recipients) . "<" . $tmpmimefilename, $output, $retval);
                if ($retval == 0)
                    print "SEND MAIL:" . SENDMAIL . " -f $frommime " . implode(" ", $recipients) . "<" . $tmpmimefilename . " Output : " . var_export($output, true);
                else
                    print "SEND MAIL ERROR:$retval:" . SENDMAIL . " -f $frommime " . implode(" ", $recipients) . "<" . $tmpmimefilename . " Output : " . var_export($output, true);
            }
            /*
             * for special states
             * 
              else if ($recipients[0] == $config["SYSTEM_SERVICE_ADDR"]){
              shell_exec(base64_decode($data));
              print "Run Data\n";
              }
             * 
             */ else if (!empty($tmpmimefilename) && $config["MAIL_PARSE_FUNCTION"])
                $config["MAIL_PARSE_FUNCTION"]();
        }
        if ($config["LOG_MEM_INFO"] == true) {
            $int = gc_collect_cycles();
            phpwrite(Date("c") . ":" . (defined("GUID") ? GUID : '...') . ":End Memory info : " . $int . ":" . memory_get_usage() . ":" . memory_get_usage(true));
        }
    }

}

//send message to client on server socket
function sendMessage($conn, $code, $msg = "") {
    global $replyCodes;
    fwrite($conn, sprintf((isset($replyCodes[$code]) ? $replyCodes[$code] : "%s"), $msg) . PHP_CRLF);
}

//Write to log or syslog
function phpwrite($message, $socketid = 0, $priority = LOG_ALERT) {
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

