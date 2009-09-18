<?php


include(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(23, new lime_output_color());


$phpExtractor = new mgI18nPhpExtractor();

$t->diag('Test with one message, and one catalogue');
$code = <<<I18N
<?php
  echo __("message_1", array(), 'blog');
I18N;

$extract = $phpExtractor->extract($code);

$t->cmp_ok(count($extract), '===', 1, '1 results found');
$t->cmp_ok($extract[0]['message'], '===', 'message_1', 'message : message_1');
$t->cmp_ok($extract[0]['catalogue'], '===', 'blog', 'catalogue : blog');


$t->diag('Test with one message, and no catalogue');
$code = <<<I18N
<?php
  echo __("message_1");
I18N;

$extract = $phpExtractor->extract($code);

$t->cmp_ok(count($extract), '===', 1, '1 results found');
$t->cmp_ok($extract[0]['message'], '===', 'message_1', 'message : message_1');
$t->cmp_ok($extract[0]['catalogue'], '===', null, 'catalogue : NULL');

$t->diag('Test with one heredoc message, and no catalogue');
$message = "Here come the heredoc message, let's see how it works";

$code = <<<I18N
<?php
  echo __(<<<HEREDOC
$message
HEREDOC
);
I18N;

$extract = $phpExtractor->extract($code);

$t->cmp_ok(count($extract), '===', 1, '1 results found');
$t->cmp_ok($extract[0]['message'], '===', $message, 'message : [long text]');
$t->cmp_ok($extract[0]['catalogue'], '===', null, 'catalogue : NULL');


$t->diag('Test with one heredoc message, and one catalogue');
$message = "Here come the heredoc message, let's see how it works";

$code = <<<I18N
<?php
  echo __(<<<HEREDOC
$message
HEREDOC
, null, 'catalogue');
I18N;

$extract = $phpExtractor->extract($code);

$t->cmp_ok(count($extract), '===', 1, '1 results found');
$t->cmp_ok($extract[0]['message'], '===', $message, 'message : [long text]');
$t->cmp_ok($extract[0]['catalogue'], '===', 'catalogue', 'catalogue : catalogue');


$t->diag('Test with mixed messages');

$messages = $phpExtractor->extract(file_get_contents(dirname(__FILE__).'/../data/test.txt'));


$t->cmp_ok(count($messages), '===', 5, '5 results found');

$t->cmp_ok($messages[0]['message'], '===', 'message_1', 'message : message_1');
$t->cmp_ok($messages[0]['catalogue'], '===', null, 'catalogue : NULL');

$t->cmp_ok($messages[1]['message'], '===', 'message_wo_catalogue_with_params', 'message : message_wo_catalogue_with_params');
$t->cmp_ok($messages[1]['catalogue'], '===', null, 'catalogue : NULL');

$t->cmp_ok($messages[2]['message'], '===', 'message_2', 'message : message_2');
$t->cmp_ok($messages[2]['catalogue'], '===', 'catalogue_1', 'catalogue : catalogue_1');

$t->cmp_ok($messages[3]['message'], '===', 'message_w_catalogue_with_params', 'message : message_w_catalogue_with_params');
$t->cmp_ok($messages[3]['catalogue'], '===', 'catalogue_2', 'catalogue : catalogue_2');

$message = "here_doc_message

lipsum bloom !!
";
$t->cmp_ok($messages[4]['message'], '===', $message, 'message : [long text]');
$t->cmp_ok($messages[4]['catalogue'], '===', 'catalogue_3', 'catalogue : catalogue_3');

