<?php
    require_once(OPEN_TABLE_OF_CONTENTS_ROOT . '/includes/utils.php');
?>

<div <?php echo get_block_wrapper_attributes(); ?>>
    <?php
    $headingsTree = get_headings_tree($attributes['minHeadingLevel'], $attributes['maxHeadingLevel']);

    if ($attributes['title']) { 
    ?>
        <p class="open-table-of-contents-title"><?php echo $attributes['title']; ?></p>
    <?php
    }

    echo render_headings($headingsTree);
    ?>
</div>