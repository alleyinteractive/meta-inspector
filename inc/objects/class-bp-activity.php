<?php
/**
 * Inspect BuddyPress activities.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for BuddyPress activities.
 */
class BP_Activity extends WP_Object {
	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'bp-activity';

	/**
	 * Initialize class.
	 */
	protected function __construct() {

		// Bail if the BuddyPress activity component is not active.
		if ( ! bp_is_active( 'activity' ) ) {
			return;
		}

		add_action( 'bp_activity_admin_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add meta boxes to the BuddyPress activity edit screen.
	 */
	public function add_meta_boxes(): void {

		$this->object_id = $this->get_id_from_request( 'aid' );
		$screen          = get_current_screen();

		if ( ! $this->object_id || ! $screen ) {
			return;
		}

		$screen_id = $screen->id;

		// Activity meta.
		add_meta_box(
			'meta-inspector-bp-activity-meta',
			__( 'Meta', 'meta-inspector' ),
			fn () => $this->render_meta_table(),
			$screen_id,
			'normal'
		);
	}
}
