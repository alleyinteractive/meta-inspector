<?php
/**
 * Plugin Name:     Meta Inspector
 * Description:     View various types of meta data about WordPress objects to assist debugging.
 * Author:          alleyinteractive, jameswalterburke
 * Author URI:      https://alley.com
 * Text Domain:     meta-inspector
 * Domain Path:     /languages
 * Version:         1.1.1
 *
 * @package         Meta_Inspector
 */

namespace Meta_Inspector;

// Load base traits.
require_once __DIR__ . '/inc/trait-singleton.php';

// Load base classes.
require_once __DIR__ . '/inc/class-table.php';
require_once __DIR__ . '/inc/class-wp-object.php';

// Load object classes.
require_once __DIR__ . '/inc/objects/class-post.php';
require_once __DIR__ . '/inc/objects/class-term.php';
require_once __DIR__ . '/inc/objects/class-user.php';
require_once __DIR__ . '/inc/objects/class-comment.php';
require_once __DIR__ . '/inc/objects/class-fm-term-meta.php';
require_once __DIR__ . '/inc/objects/class-bp-group.php';

// Initalize classes.
add_action(
	'plugins_loaded', // Load early so we can use other hooks.
	function() {
		/**
		 * Filter the capability needed to view the meta boxes.
		 *
		 * @param string $capability The capability needed to view the meta boxes.
		 */
		if ( ! current_user_can( apply_filters( 'meta_inspector_capability', 'manage_options' ) ) ) {
			return;
		}

		Post::instance();
		Term::instance();
		User::instance();
		Comment::instance();
		// Legacy FM Term Meta Data support.
		Fm_Term_Meta::instance();

		// BuddyPress support.
		if ( class_exists( 'BuddyPress' ) ) {
			BP_Group::instance();
		}
	}
);
