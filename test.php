<?php

include 'class.smtp.php';

$smptclient = new SMTP();
$smptclient->Connect("10.35.24.120",8000);
$smptclient->Hello();
$smptclient->Mail("hasan.ucak@hs01.kep.tr");
$smptclient->Recipient("hasan.ucak@gmail.com");
$smptclient->Data("sample mail");
$smptclient->Quit();