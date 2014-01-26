<?php

return array(
  'Slim' => array(
        'cookies.encrypt' => true,
        'cookies.secret_key' => 'CHANGE_ME',
        'cookies.cipher' => MCRYPT_RIJNDAEL_256,
        'cookies.cipher_mode' => MCRYPT_MODE_CBC,
    ),
  'Mongo' => array(
        'uri' => 'mongodb://architect:7o5IDIybhGZJHawsvy7fZCSUTdJPO0Ee41KSlwhC3L6EdhXGQA7qNFrKO6tvbdj@paulo.mongohq.com:10014/response_alpha',
    ),
);
