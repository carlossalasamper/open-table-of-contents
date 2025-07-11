<?php

/**
 *  Sanitizes a text so it can be used as an HTML ID.
 *
 * @return string sanitized text.
 */
function sanitize_text($text) {
    return sanitize_title($text);
}

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
        $dom = new DOMDocument('1.0', 'UTF-8');

        libxml_use_internal_errors(true);
        $dom->loadHTML(mb_convert_encoding($post_content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_use_internal_errors(false);

        $xpath = new DOMXPath($dom);

        $headingTagNames = array_map(function ($level) {
            return 'h' . $level;
        }, range($minHeadingLevel, $maxHeadingLevel));

        $query = '//'.implode('|//', $headingTagNames);
        $headingsOfType = $xpath->query($query);

        foreach ($headingsOfType as $heading) {
            $headingText = $heading->textContent;
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
    $treeContext = array();

    foreach ($headings as $heading) {
        $headingLevel = $heading['level'];
        $headingText = $heading['text'];

        if ($headingLevel === $minHeadingLevel) {
            $tree[] = $heading;
            $treeContext = array($headingLevel => &$tree[count($tree) - 1]);
        } else {
            $parentHeadingLevel = $headingLevel - 1;
            $parentHeading = &$treeContext[$parentHeadingLevel];

            if ($parentHeading) {
                $parentHeading['children'][] = $heading;
                $treeContext[$headingLevel] = &$parentHeading['children'][count($parentHeading['children']) - 1];
                $treeContext = array_filter($treeContext, function ($index) use ($headingLevel) {
                    return is_numeric($index) && $index <= $headingLevel;
                }, ARRAY_FILTER_USE_KEY);
            }
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
        echo '<a href="#' . sanitize_text($heading['text']) . '">' . $heading['text'] . '</a>';

        if (!empty($heading['children'])) {
            render_headings($heading['children']);
        }

        echo '</li>';
    }

    echo '</ul>';
}


function add_id_to_headings($content) {
    $modifiedContent = $content;

    if (has_table_of_contents_block($content)) {
        // Load the HTML content into a DOMDocument
        $dom = new DOMDocument('1.0', 'UTF-8');
        @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8')); // Use @ to suppress warnings about malformed HTML
        $dom->encoding = 'utf-8';

        // Create a DOMXPath instance to query the document
        $xpath = new DOMXPath($dom);

        // Query all heading elements (h1, h2, h3, etc.)
        $headings = $xpath->query('//h1|//h2|//h3|//h4|//h5|//h6');

        $headingCounter = array();

        foreach ($headings as $heading) {
            // Get the text content of the heading
            $headingText = $heading->textContent;

            // Prepare a sanitized version of the heading text for use as an ID
            $headingId = sanitize_text($headingText);

            // Check if the ID is already taken, and if so, append a number
            $counter = isset($headingCounter[$headingId]) ? ++$headingCounter[$headingId] : 1;
            $uniqueHeadingId = $counter > 1 ? "{$headingId}-{$counter}" : $headingId;

            // Set the ID attribute of the heading element
            $heading->setAttribute('id', $uniqueHeadingId);

            // Update the heading ID counter
            $headingCounter[$headingId] = $counter;
        }

        // Save the modified HTML content
        $modifiedContent = $dom->saveHTML();
    }

    return $modifiedContent;
}
add_filter('the_content', 'add_id_to_headings');

function has_table_of_contents_block($content) {
    // Load the HTML content into a DOMDocument
    $dom = new DOMDocument('1.0', 'UTF-8');
    @$dom->loadHTML($content); // Use @ to suppress warnings about malformed HTML

    // Create a DOMXPath instance to query the document
    $xpath = new DOMXPath($dom);

    // Define the XPath query for the custom block
    $query = '//div[@data-id="' . OPEN_TABLE_OF_CONTENTS_BLOCK_ID .  '"]';

    // Perform the query
    $result = $xpath->query($query);

    // Check if any matching elements were found
    return $result->length > 0;
}
?>