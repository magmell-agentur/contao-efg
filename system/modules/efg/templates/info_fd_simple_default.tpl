
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>
<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<div class="single_record">

<?php foreach ($this->listItem as $fields): ?>
<div class="field <?php echo $fields['class']?>"><?php echo $fields['label']; ?>:&nbsp;<?php if ($fields['type']=='file' && $fields['src']):?><?php if ($fields['display']=='image'): ?><img src="<?php echo $this->getImage($fields['src'], 160, null); ?>" alt="<?php echo urldecode(basename($fields['src'])); ?>" title="<?php echo urldecode(basename($fields['src'])); ?>" /> <?php else: ?><img src="<?php echo $fields['icon']; ?>" alt="<?php echo $fields['linkTitle']; ?>" />&nbsp;<a href="<?php echo $fields['href']; ?>"><?php echo $fields['linkTitle'] . $fields['size']; ?></a><?php endif; ?>
<?php else: echo $fields['content'].'&nbsp;'; endif;?></div>
<?php endforeach; ?>

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

</div>
