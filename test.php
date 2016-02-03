<?php

function smtp_shutdown() {
    //global $hatabildirimlistesi;
    $error = error_get_last();
    if ($error['type'] === E_ERROR || $error['type'] == E_USER_ERROR) {
        $debug = debug_backtrace(true);
        $message = "SMTP Sunucuda Fatal Error Oluştu : Error  : " . var_export($error, true) . " Debug:" . var_export($debug, true) . "</br>Sunucun Bilgileri Adı->" . SERVERNAME . " IP->" . SERVERNAME;
        print PHP_EOL . "FATAL : " . Date("c") . ":" . (defined("GUID") ? GUID : '...') . ":" . $message;
//        $ret = errorSend("GENEL", $message);
    }
    print PHP_EOL . "ERROR : " . Date("c") . ":" . (defined("GUID") ? GUID : '...') . ":" . var_export($error, true);
}

function yakala($str){
    print "Yakaladım. : $str";
}

//register_shutdown_function('smtp_shutdown');
set_error_handler('yakala', 0);

try {
    $con = new mysqli("localhost","root","18561229","kepdb");
    $con->query("select now()");
    print "deneme";
} catch (Exception $exc) {
    echo "Exceptionnnnn ... :" . $exc->getTraceAsString();
}




exit();
error_reporting(E_ALL);
set_time_limit(5);
ini_set('memory_limit', '256M');
$arrayLarge = array_fill(0, 1010911, '*');
echo "Running 5/50 (get_defined_vars).\n";
$array_get_defined_vars_5 = get_defined_vars();
echo "Running 14/50 (array_merge).\n";
$array_array_merge_14 = array_merge($arrayLarge, $array_get_defined_vars_5);
//$array_array_merge_14 = array_merge($array_array_merge_14, $array_get_defined_vars_5);
echo "Running 30/50 (exec).\n";
//$arrayLarge = array();
$string_exec_30 = exec("ls -lrth",$output, $arrayLarge);

if (is_array($arrayLarge))
    print "Array";
if (is_int($arrayLarge))
    print "int---";

var_export($arrayLarge);

exit();



$rbuffer = "...";

                    if (substr($rbuffer,0,2)=='..' && strlen($rbuffer)>2)
                            $rbuffer = substr($rbuffer, 1);

print $rbuffer;

exit();
$buffer = strtolower("RCPT TO:<pttkep.test@hs01.kep.tr> ORCPT=rfc822;pttkep.test@hs01.kep.tr");
if (preg_match_all('/^rcpt to:(\s|)(<(.*)>|.*)/', $buffer, $matches, PREG_SET_ORDER)) {
                    $address = (isset($matches[0][3])?$matches[0][3]:$matches[0][2]);
                    if (filter_var($address, FILTER_VALIDATE_EMAIL) === FALSE)
                        print  "501 invalid mail address ";
                    else {
                        $recipients[] = $address;
                       print "OK";
                    }
              
                }else print "filtererrrr";

exit();
include 'class.smtp.php';

$smptclient = new SMTP();
$smptclient->Connect("localhost",8000);
$smptclient->Hello();
$smptclient->Mail("hasan.ucak@hs01.kep.tr");
$smptclient->Recipient("hasan.ucak@gmail.com");
$smptclient->Data("sample mail");
$smptclient->Quit();