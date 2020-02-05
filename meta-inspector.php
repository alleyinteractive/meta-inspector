<?php
/**
 * Plugin Name:     Meta Inspector
 * Description:     View various types of meta data about WordPress objects to assist debugging.
 * Author:          alley, jameswalterburke
 * Author URI:      https://alley.co
 * Text Domain:     meta-inspector
 * Domain Path:     /languages
 * Version:         1.0.0
 *
 * @package         Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Current version.
 */
define( 'META_INSPECTOR_VERSION', '1.0.0' );

/**
 * Filesystem path.
 */
define( 'META_INSPECTOR_PATH', dirname( __FILE__ ) );

// Load base traits.
require_once META_INSPECTOR_PATH . '/inc/trait-singleton.php';

// Load base classes.
require_once META_INSPECTOR_PATH . '/inc/class-table.php';
require_once META_INSPECTOR_PATH . '/inc/class-wp-object.php';

// Load object classes.
require_once META_INSPECTOR_PATH . '/inc/objects/class-post.php';
require_once META_INSPECTOR_PATH . '/inc/objects/class-term.php';
require_once META_INSPECTOR_PATH . '/inc/objects/class-user.php';
require_once META_INSPECTOR_PATH . '/inc/objects/class-comment.php';
require_once META_INSPECTOR_PATH . '/inc/objects/class-fm-term-meta.php';

// Initalize classes.
add_action(
	'plugins_loaded', // Load early so we can use other hooks.
	function() {
		// Admins only.
		if ( current_user_can( 'manage_options' ) ) {
			Post::instance();
			Term::instance();
			User::instance();
			Comment::instance();
			// Legacy FM Term Meta Data support.
			Fm_Term_Meta::instance();
		}
	}
);
