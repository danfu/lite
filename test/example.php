<?php

require '../class/lite.class.php';

$tpl = new Template('example.tpl');

$articles = array(
1 => 'apple',
2 => 'bananas'
);

foreach ($articles as $a_id => $a_name) {

/* assign variables */

$tpl->assign(
array(
'article_id' => $a_id,
'article_name' => $a_name
));

/* parse article block and append output to article */

$tpl->parse('article', 'article', 1);

}

$tpl->parse();

$tpl->output();

?>