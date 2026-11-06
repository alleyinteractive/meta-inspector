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
	protected function __construct() {
		add_action( 'edit_user_profile', [ $this, 'add_meta_boxes' ], 1000 );
		add_action( 'show_user_profile', [ $this, 'add_meta_boxes' ], 1000 );
	}

	/**
	 * Add meta boxes to the post edit screen.
	 */
	public function add_meta_boxes(): void {

		if (
			defined( 'IS_PROFILE_PAGE' )
			&& IS_PROFILE_PAGE
		) {
			$this->object_id = get_current_user_id();
		} else {
			$this->object_id = $this->get_id_from_request( 'user_id' );
		}

		if ( ! $this->object_id ) {
			return;
		}

		// Render table of meta data.
		$this->render_meta_table( __( 'User Meta', 'meta-inspector' ) );
	}
}
