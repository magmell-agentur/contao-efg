
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<table cellpadding="2" cellspacing="0" border="0" class="single_record" summary="">
<tbody><?php foreach ($this->record as $class => $col): ?> 
  <tr class="<?php echo $class; ?>">
    <td class="label"><?php echo $col['label']; ?>:&nbsp;</td>
    <td class="value"><?php if ($col['type']=='file' && $col['src']): ?><?php if ($col['display']=='image'): ?><img src="<?php echo($this->getImage($col['src'], 160, null)); ?>" alt="<?php echo urldecode(basename($col['src'])); ?>" title="<?php echo urldecode(basename($col['src'])); ?>" /> <?php else: ?><img src="<?php echo $col['icon']; ?>" alt="<?php echo $col['linkTitle']; ?>" />&nbsp;<a href="<?php echo $col['href']; ?>"><?php echo $col['linkTitle'] . $col['size']; ?></a><?php endif; ?>
    <?php else: echo $col['content'].'&nbsp;'; endif; ?>
    </td>
  </tr><?php endforeach; ?> 
</tbody>
</table>

<?php if ($this->editAllowed): ?>
<div class="fd_edit"><a href="<?php echo $this->link_edit; ?>" class="fd_edit" title="<?php echo $this->textlink_edit[1]; ?>"><?php echo $this->textlink_edit[0]; ?></a></div>
<?php endif; ?>

<?php if ($this->deleteAllowed): ?>
<div class="fd_delete"><a href="<?php echo $this->link_delete; ?>" class="fd_delete" onclick="if (!confirm('<?php echo $this->text_confirmDelete; ?>')) return false;" title="<?php echo $this->textlink_delete[1]; ?>"><?php echo $this->textlink_delete[0]; ?></a></div>
<?php endif; ?>

<?php if ($this->exportAllowed): ?>
<div class="fd_export"><a href="<?php echo $this->link_export; ?>" class="fd_export" title="<?php echo $this->textlink_export[1]; ?>"><?php echo $this->textlink_export[0]; ?></a></div>
<?php endif; ?>

<div class="go_back">{{link::back}}</div>
</div>
