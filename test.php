<?php

$data = 'a:1:{i:0;s:9:"IVA|13.00";}';
$r = unserialize($data);
var_dump($r);