<?php

/* lite Template Engine
no license - do whatever you want ;)

requires PHP 5.3 or later

see test/example.php for how to use this

2012 daniel fuehrer (daniel[at]network.de)

*/

class Template {

var $template;
var $template_id;
var $file_path;
var $basename;
var $dirname;
var $vars;
var $block;
var $parsed_block;
var $parent = array();
var $child = array();
var $set_blocks = array();

/* directory where template are stored */

var $template_dir = './tpl';

/* directory where compiled templates are stored */

var $compiled_dir = './tpl/php';

function __construct($file) {

$path = pathinfo($file);

$this->template_id = '_'.$path['filename'];

$this->dirname = $path['dirname'];

$this->basename = $path['basename'];

$this->file_path = $this->template_dir.'/'.$file;

$compiled_file = $this->compiled_dir.'/'.$this->dirname.'/'.$this->basename.'.php';

if (file_exists($compiled_file) and filemtime($compiled_file) >= filemtime($this->file_path)) {

include $compiled_file;

if ($parent) $this->parent = &$parent;

if ($child) $this->child = &$child;

} else {

$this->template = file_get_contents($this->file_path);

$this->compile();

include $compiled_file;

}

}

/* compiles template into a php file and stores it in compiled_dir */

private function compile() {

$this->find_vars();

$this->find_blocks();

$output .= '<?php namespace '.$this->template_id.';';

foreach ($this->parent as $parent_key => $parent_val) {

$output .= '$parent[\''.$parent_key.'\'] = \''.$parent_val.'\';';

foreach ($this->child[$parent_val] as $child_key => $child_val) {

$output .= '$child[\''.$parent_val.'\'][\''.$child_key.'\'] = \''.$child_val.'\';';

}

}

$output .= '?>';

foreach ($this->block as $handle => $block) {

$function_pre = '<?php function '.$handle.'(&$vars) { ?>';

$function_post = '<?php } ?>';

$function_block = $function_pre . $block . $function_post;

$output .= $function_block;

}

$output .= '<?php function main(&$vars) { ?>';

$output .= $this->template;

$output .= '<?php } ?>';

file_put_contents($this->compiled_dir.'/'.$this->dirname.'/'.$this->basename.'.php', $output);

}

private function find_vars() {

$regexp = "/\{([0-9A-Za-z_-]+)\}/sm";

preg_match_all($regexp, $this->template, $match);

$this->template = preg_replace($regexp, '<?=$vars[\'\\1\']?>', $this->template);

}

/* remove block */

function unset_block($handle) {

unset ($this->block[$handle]);
unset ($this->vars[$handle]);

}

/* recursively find blocks in template */

private function find_blocks($handle = null) {

$regexp = "/<!--\s+BEGIN\s+([0-9A-Za-z_-]+)+\s+-->(.*)<!--\s+END\s+\\1+\s+-->/sm";

if (is_null($handle)) {

$section = &$this->template;

} else {

$section = &$this->block[$handle];
  
}

if (preg_match_all($regexp, $section, $match, PREG_SET_ORDER)) {
     
for ($i = 0; $i < count($match); $i++) {
     
$this->block[$match[$i][1]] = $match[$i][2];
      
$section = str_replace($match[$i][0], '<?=$vars[\'' . $match[$i][1] . '\'];?> ', $section); // space after closing tag will prevent newline from being ignored

if (!is_null($handle)) { 

if (!in_array($handle, $this->parent)) {
     
$this->parent[] = $handle;

}

$this->child[$handle][] = $match[$i][1];

}

$this->find_blocks($match[$i][1]);

}

}

}

private function set_var($var_name, $var_value = null) {

$this->vars[$var_name] = $var_value;

}

/* parse block $handle and copy/append output to block $target */

function parse($handle = null, $target = null, $append = false) {

if (!is_null($handle)) {

ob_start();

call_user_func($this->template_id.'\\'.$handle, &$this->vars);

$parsed_block[$handle] = ob_get_contents();

ob_end_clean();

if (in_array($handle, $this->parent)) {

$array = $this->child[$handle];

for ($x = 0; $x < count($array); $x++)  {

unset($this->vars[$this->child[$handle][$x]]);

}

}

if ($append) {

if (!empty($this->vars[$target])) {

$this->vars[$target] .= $parsed_block[$handle];


} else {

$this->vars[$target] = $parsed_block[$handle];

}

} elseif (!empty($handle)) {

$this->vars[$target] = $parsed_block[$handle];

}

} else {

ob_start();

call_user_func($this->template_id.'\\main', &$this->vars);

$this->template = ob_get_contents();

ob_end_clean();

}

return $parsed_block[$handle];

}

/* assign variables */

public function assign(array $vars) {

while (($value = current($vars)) !== false) {

$this->set_var(key($vars), $value);

next($vars);

}

return true;

}

function output() {

echo $this->template;

}

}

?>