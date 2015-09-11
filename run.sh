PID=`pgrep -lf phpsmtpserver|cut -d " " -f1`
kill -9 $PID
while true; 
do
          #if have recently  zend_mm_heap corrupted error ..this is set
          #php will not use zend memory management
           export USE_ZEND_ALLOC=0
          /opt/php/bin/php /opt/smtp/phpsmtpserver.php >> /opt/smtp/phpsmtpserver.log 2>&1
done
