<?php
/**
 * Gets all the headings of a post and returns an array of them.
 *
 * @param int $minHeadingLevel The minimum heading level to include.
 * @param int $maxHeadingLevel The maximum heading level to include.
 * @return array An array of headings.
 */
function get_headings_array($minHeadingLevel, $maxHeadingLevel) {
    $post_content = get_the_content();
    $headings = array();

    if (!empty($post_content)) {
        $dom = new DOMDocument;

        libxml_use_internal_errors(true);
        $dom->loadHTML($post_content, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false);

        $xpath = new DOMXPath($dom);

        $headingTagNames = array_map(function ($level) {
            return 'h' . $level;
        }, range($minHeadingLevel, $maxHeadingLevel));

        $query = '//'.implode('|//', $headingTagNames);
        $headingsOfType = $xpath->query($query);

        foreach ($headingsOfType as $heading) {
            $headingText = $heading->nodeValue;
            $headingLevel = (int)substr($heading->tagName, 1);

            $headings[] = array(
                'text' => $headingText,
                'level' => $headingLevel,
            );
        }
    }

    return $headings;
}


/**
 * Gets all the headings of a post and returns a data structure with them organized hierarchically.
 *
 * @param int $minHeadingLevel The minimum heading level to include.
 * @param int $maxHeadingLevel The maximum heading level to include.
 * @return array An array representing the hierarchical structure of headings.
 */
function get_headings_tree($minHeadingLevel, $maxHeadingLevel) {
    $headings = get_headings_array($minHeadingLevel, $maxHeadingLevel);
    $tree = array();

    foreach ($headings as $heading) {
        $level = $heading['level'];
        $item = array('text' => $heading['text'], 'children' => array());

        if ($level === $minHeadingLevel) {
            $tree[] = $item;
        } else {
            $parent = &$tree;

            for ($i = $minHeadingLevel + 1; $i <= $level; $i++) {
                $parent = &$parent[count($parent) - 1];
            }

            $parent['children'][] = $item;
        }
    }

    return $tree;
}

/**
 * Renders the headings of a post or page hierarchically.
 * 
 * @param array $headings An array representing the hierarchical structure of headings.
 */
function render_headings($headings) {
    if (empty($headings)) {
        return;
    }

    echo '<ul>';

    foreach ($headings as $heading) {
        echo '<li>';
        echo '<a href="#' . sanitize_title($heading['text']) . '">' . $heading['text'] . '</a>';

        if (!empty($heading['children'])) {
            render_headings($heading['children']);
        }

        echo '</li>';
    }

    echo '</ul>';
}
?>