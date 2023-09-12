<?php
/**
 * Inspect comments.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for comments.
 */
class Comment extends WP_Object {
	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'comment';

	/**
	 * Initialize class.
	 */
	protected function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add meta boxes to the comment edit screen.
	 */
	public function add_meta_boxes() {

		// Bail if we don't have comments.
		if ( ! have_comments() ) {
			return;
		}

		// Store comment ID.
		$this->object_id = get_comment_ID();

		// Comment meta.
		add_meta_box(
			'meta-inspector-comment-meta',
			__( 'Comment Meta', 'meta-inspector' ),
			[ $this, 'render_meta' ],
			$this->type,
			'normal'
		);
	}

	/**
	 * Render a table of comment meta.
	 */
	public function render_meta() {
		$this->render_meta_table();
	}
}
