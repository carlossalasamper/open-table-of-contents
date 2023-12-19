<?php
/**
 * Gets all the headings of a post and return a data structure with them organized hierarchically.
 */
function get_headings_from_content() {
    $post_content = get_the_content();
    $headings = array();
    $currentLevel = 0;
    $currentItem = null;

    if (!empty($post_content)) {
        $dom = new DOMDocument;

        libxml_use_internal_errors(true); // Enable error handling for poorly formed HTML
        $dom->loadHTML($post_content);
        libxml_use_internal_errors(false); // Disable error handling
        
        for ($i = 1; $i <= 6; $i++) {
            $headingTagName = 'h' . $i;
        
            $headingsOfType = $dom->getElementsByTagName($headingTagName);
        
            foreach ($headingsOfType as $heading) {
                $headingText = $heading->nodeValue;
        
                // Create a new item for each heading
                $item = array(
                    'text' => $headingText,
                    'children' => array(),
                );
        
                // Determine heading level (e.g., h1, h2, h3, etc.)
                $headingLevel = $i;
        
                // Find the correct parent for the current heading based on order
                while ($currentItem !== null && $currentLevel >= $headingLevel) {
                    $currentItem = &$currentItem['parent']; // Move up the hierarchy
                    $currentLevel--;
                }
        
                // Append the new item to the parent
                if ($currentItem === null) {
                    // This is a top-level heading
                    $headings[] = $item;
                } else {
                    $currentItem['children'][] = $item;
                }
        
                // Update current item and level for the next iteration
                $currentItem = &$item;
                $currentLevel = $headingLevel;
            }
        }    
    }

    return $headings;
}

/**
 * Renders the headings of a post or page recursively.
 */
function render_headings() {
    $headings = get_headings_from_content();

    if (!empty($headings)) {
        $stack = array($headings);
        $output = '';

        while (!empty($stack)) {
            $current = array_pop($stack);

            foreach ($current as $heading) {
                $output .= '<li>';
                $output .= '<a href="#' . sanitize_title($heading['text']) . '">' . $heading['text'] . '</a>';

                if (!empty($heading['children'])) {
                    $stack[] = $heading['children'];
                }

                $output .= '</li>';
            }
        }

        echo '<ul>' . $output . '</ul>';
    }
}
?>