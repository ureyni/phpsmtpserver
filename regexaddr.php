<?php

/*
 * "hasan ucak" <hasan.ucak@gmail.com>
 * "hasan ucak"<hasan.ucak@gmail.com>
 * hasan.ucak@gmail.com
 * hasan.ucak@subdomain.gmail.com
 *   (?(?=[^a-z]*[a-z])
  \d{2}-[a-z]{3}-\d{2}  |  \d{2}-\d{2}-\d{2} )
 */
$str = '"hasan ucak" <hasan.ucak@gmail.com>';
if (preg_match_all('/(?(?=^\")(^\"(.*)\")([\s]+|)\<([\._a-z0-9-]+)@(gmail.com|eimza.gmail.com|yahoo.com|hotmail.com)\>$|'
                . '(^[\._a-z0-9-]+)@(gmail.com|eimza.gmail.com|yahoo.com|hotmail.com)$)/', $str, $matches, PREG_SET_ORDER)||
        preg_match_all('/(?(?=^\")(^\"(.*)\")([\s]+|)\<([\._a-z0-9-]+)@([_a-z0-9-]+)\.(gmail.com|eimza.gmail.com|yahoo.com|hotmail.com)\>$|'
                . '(^[\._a-z0-9-]+)@([_a-z0-9-]+)\.(gmail.com|eimza.gmail.com|yahoo.com|hotmail.com)$)/', $str, $matches, PREG_SET_ORDER)
        )
    print "valid";
else
    print "invalid";
print ".............\n" . var_export($matches,true);
