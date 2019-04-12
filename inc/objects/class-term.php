<?php
/**
 * Inspect terms.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for terms.
 */
class Term extends WP_Object {

	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'term';

	/**
	 * Initialize class.
	 */
	public function setup() {
		add_action( 'registered_taxonomy', [ $this, 'tax_edit_form_action' ], 1000 );
	}

	/**
	 * Add meta boxes to the term edit screen.
	 *
	 * @param string $taxonomy Taxonomy slug.
	 */
	public function tax_edit_form_action( $taxonomy ) {
		add_action( "{$taxonomy}_edit_form", [ $this, 'add_meta_boxes' ], 1000 );
	}

	/**
	 * Render a table of post meta.
	 */
	public function add_meta_boxes() {

		// Ensure the term_id is set.
		if ( ! isset( $_GET['tag_ID'] ) ) {
			return;
		}

		// Store term id.
		$this->object_id = absint( $_GET['tag_ID'] );

		// Render table of meta data.
		$this->render_meta_table( __( 'Term Meta', 'meta-inspector' ) );
	}
}
