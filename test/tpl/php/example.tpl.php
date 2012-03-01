<?php namespace _example;?><?php function article(&$vars) { ?>
<tr>
<td><?=$vars['article_id']?><td>
<td><?=$vars['article_name']?></td>
</tr>
<?php } ?><?php function main(&$vars) { ?><h1>List of articles:<h1>

<table>
<tr>
<th>ID</th>
<th>Name</th>
</tr>
<?=$vars['article'];?> 
</table><?php } ?>