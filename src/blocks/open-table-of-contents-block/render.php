<?php
    require_once(OPEN_TABLE_OF_CONTENTS_ROOT . '/includes/utils.php');
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php if ($attributes['title']) { ?>
        <p class="open-table-of-contents-title"><?php echo $attributes['title']; ?></p>
    <?php } ?>

    <?php echo render_headings(get_headings_from_content()); ?>
</div>