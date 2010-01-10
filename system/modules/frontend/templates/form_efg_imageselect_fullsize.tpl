<div class="imageselect block">
<table cellspacing="0" cellpadding="0" summary="Image gallery">
<?php foreach ($this->body as $class=>$row): ?> 
<tr class="<?php echo $class; ?>"><?php foreach ($row as $col): if ($col['hasImage']): ?> 
  <td class="<?php echo $col['class']; ?>" style="width:<?php echo $col['colWidth']; ?>;"><div class="image_container"<?php if ($col['margin']): ?> style="<?php echo $col['margin']; ?>"<?php endif; ?>><a href="<?php echo $col['href']; ?>" rel="lightbox[<?php echo $this->lightboxId; ?>]" title="<?php echo $col['alt']; ?>"><img src="<?php echo $col['src']; ?>"<?php echo $col['imgSize']; ?> alt="<?php echo $col['alt']; ?>" /></a><?php if ($col['caption']): ?><div class="caption"><?php echo $col['caption']; ?></div><?php endif; ?><div><input type="radio" name="<?php echo $col['optName']; ?>" id="<?php echo $col['optId']; ?>" class="radio<?php if (strlen($this->class)): echo ' '.$this->class; endif; ?>" value="<?php echo $col['srcFile']; ?>" <?php echo $col['checked']; ?>/></div></div></td><?php else: ?> 
  <td class="<?php echo $col['class']; ?> empty">&nbsp;</td><?php endif; endforeach; ?> 
</tr><?php endforeach; ?> 
</table>
<?php echo $this->pagination; ?>
</div>