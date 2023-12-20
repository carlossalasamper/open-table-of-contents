<?php
/**
 * Plugin Name:       Open Table Of Contents
 * Description:       🗂️ The open source WordPress plugin to insert Table of Contents into your posts and pages.
 * Requires at least: 6.1
 * Requires PHP:      7.0
 * Version:           0.1.0
 * Author:            Carlos Sala Samper
 * License:           MIT
 * License URI:       https://opensource.org/license/mit/
 * Text Domain:       open-table-of-contents
 * 
 * @package           open-table-of-contents
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Defines the root directory of the plugin.
 */
define('OPEN_TABLE_OF_CONTENTS_ROOT', __DIR__);

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 *
 * @see https://developer.wordpress.org/reference/functions/register_block_type/
 */
function open_table_of_contents_open_table_of_contents_block_init() {
    register_block_type( __DIR__ . '/blocks/table-of-contents-block');
}
add_action( 'init', 'open_table_of_contents_open_table_of_contents_block_init' );
