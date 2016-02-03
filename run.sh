PID=`pgrep -lf phpsmtpserver|cut -d " " -f1`
kill -9 $PID
/opt/php56/bin/php /opt/smtp/phpsmtpserver.php >> /opt/smtp/phpsmtpserver.log 2>&1 &
PID=`echo $!`
echo $PID>/opt/smtp/phpsmtpserver.pid
while true; do
#Control Process each 60 second
   sleep 60
   PID=`pgrep -lf phpsmtpserver|cut -d " " -f1`
   if [ -z "$PID" ]; then
       echo "not found Process ID( $PID ),try run again"
       export USE_ZEND_ALLOC=0
       /opt/php/bin/php /opt/smtp/phpsmtpserver.php >> /opt/smtp/phpsmtpserver.log 2>&1 &
       PID=`echo $!
       echo $PID>/opt/smtp/phpsmtpserver.pid
   fi
done