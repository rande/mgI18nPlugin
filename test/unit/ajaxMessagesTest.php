<?php

include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(2, new lime_output_color());


$merged = array(
  array('catalogue' => 'catalogue2', 'message' => 'the message', 'params' => array())
);

$messages = array(
  'catalogue1' => array(
    array('message1', array('param1', 'param2')),
    array('message2'),
    array('message3'),
  ),

  'catalogue2' => array(
    array('message1', array('param1')),
    array('message2'),
  )
);

$merged = mgI18nMessageHelper::appendMessages($merged, $messages);

$t->cmp_ok(count($merged), '===', 6, '6 elements in the array');
$t->cmp_ok($merged[5]['catalogue'], '===', 'catalogue2', 'catalogue2');

