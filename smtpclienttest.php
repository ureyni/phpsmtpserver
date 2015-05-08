<?php
/*
 * SMTP sample with pctnl
 */
@$str = '';
@$status = 'OK';
@$socket = stream_socket_client("tcp://10.35.24.120:25", $errno, $errstr, 30);
if (!$socket) {
    echo "$errstr ($errno)<br />\n";
}
define('CRLF', "\r\n");
$pid = pcntl_fork();
fwrite($socket, "ehlo 10.35.24.120"  . CRLF);

if ($pid === -1) {
    print "\n errror";
    exit; // failed to fork
} elseif ($pid === 0) {
    // read to socket ..... freedommm!!
    while (!feof($socket)) {
        $str = fgets($socket, 1024);
        print $str;
    }
} else {
    //send to smtp command to smtp server ...freedommm
    sleep(3);
    fwrite($socket, "MAIL FROM:<hasan.ucak@gmail.com>" . CRLF);
    fwrite($socket, "RCPT TO:<hasan.ucak@gmail.com>" . CRLF);
    fwrite($socket, "quit" . CRLF);

    pcntl_wait($status);
}
