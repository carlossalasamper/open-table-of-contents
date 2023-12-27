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
            $headingText = utf8_decode($heading->textContent);
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
    $processedHeadings = array();

    // Helper function to recursively build the tree
    $build_tree = function($headings, $minLevel, $maxLevel, &$processedHeadings) use (&$build_tree) {
        $tree = array();

        foreach ($headings as $heading) {
            if ($heading['level'] === $minLevel && !in_array($heading, $processedHeadings, true)) {
                $processedHeadings[] = $heading; // Mark heading as processed

                $item = array('text' => $heading['text'], 'children' => array());
                $item['children'] = $build_tree($headings, $minLevel + 1, $maxLevel, $processedHeadings);
                $tree[] = $item;
            }
        }

        return $tree;
    };

    // Start building the tree from the minimum heading level
    return $build_tree($headings, $minHeadingLevel, $maxHeadingLevel, $processedHeadings);
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
        $dom = new DOMDocument();
        @$dom->loadHTML(utf8_decode($content)); // Use @ to suppress warnings about malformed HTML
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
    $dom = new DOMDocument();
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