<?php
/**
 * Inspect users.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for users.
 */
class User extends WP_Object {

	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'user';

	/**
	 * Initialize class.
	 */
	public function setup() {
		add_action( 'edit_user_profile', [ $this, 'add_meta_boxes' ], 1000 );
		add_action( 'show_user_profile', [ $this, 'add_meta_boxes' ], 1000 );
	}

	/**
	 * Add meta boxes to the post edit screen.
	 */
	public function add_meta_boxes() {

		if (
			defined( 'IS_PROFILE_PAGE' )
			&& IS_PROFILE_PAGE
		) {
			$this->object_id = get_current_user_id();
		} elseif ( isset( $_GET['user_id'] ) ) {
			$this->object_id = absint( $_GET['user_id'] );
		} else {
			return;
		}

		// Render table of meta data.
		$this->render_meta_table( __( 'User Meta', 'meta-inspector' ) );
	}
}
