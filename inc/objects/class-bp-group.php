<?php
/**
 * Inspect groups.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for a BuddyPress group.
 */
class BP_Group extends WP_Object {
	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'bp-group';

	/**
	 * Initialize class.
	 */
	protected function __construct() {

		// Bail if BuddyPress groups are not active.
		if ( ! bp_is_active( 'groups' ) ) {
			return;
		}

		add_action( 'bp_groups_admin_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add meta boxes to the BuddyPress group edit screen.
	 */
	public function add_meta_boxes() {

		// Store group ID.
		$this->object_id = (int) sanitize_text_field( wp_unslash( $_GET['gid'] ?? 0 ) );

		// Group meta.
		add_meta_box(
			'meta-inspector-bp-group-meta',
			__( 'Meta', 'meta-inspector' ),
			[ $this, 'render_meta' ],
			get_current_screen()->id,
			'normal'
		);
	}

	/**
	 * Render a table of group meta.
	 */
	public function render_meta() {
		$this->render_meta_table();
	}
}
