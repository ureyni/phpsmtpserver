PID=`pgrep -lf phpsmtpserver|cut -d " " -f1`
kill -9 $PID
while true;do
/opt/php56/bin/php /opt/smtp/phpsmtpserver.php >> /opt/smtp/phpsmtpserver.log 2>&1
done
