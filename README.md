# phpsmtpserver
Simple SMTP server with php 
<br>
Requirements

PHP5

sockets (http://www.php.net/manual/en/sockets.installation.php)

pthreads (http://pecl.php.net/package/pthreads)

Run

sh run.sh

php.ini----

memory_limit = 4096M
#if this get error zend_heap_memory corrupt 
zend.enable_gc = Off
report_memleaks = Off
report_zend_debug = Off

---------------------

