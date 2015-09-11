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
#if this get error zend_mm_heap corrupted 
zend.enable_gc = Off
report_memleaks = Off
report_zend_debug = Off
---------------------------------
root@localhost#export USE_ZEND_ALLOC=0

---------------------
change zend_varible.c and recompile php....

    134                         ALLOC_HASHTABLE_REL(tmp_ht);
    135                         zend_hash_init(tmp_ht, zend_hash_num_elements(original_ht), NULL, ZVAL_PTR_DTOR, 0);
    136                         zvalue->value.ht = tmp_ht;
    137                         zend_hash_copy(tmp_ht, original_ht, (copy_ctor_func_t) zval_add_ref, (void *) &tmp, si        zeof(zval *));
    138                                 tmp_ht->nNextFreeElement = original_ht->nNextFreeElement;


