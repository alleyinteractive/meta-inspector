<?php
/**
 * Provides a table showing FM Term Meta data for sites
 * that have this legacy type of Fieldmanager meta data
 * (prior to term meta existing in WP Core).
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for terms.
 */
class Fm_Term_Meta extends WP_Object {

	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'fm-term-meta';

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
		$this->render_meta_table( __( 'FM Term Meta', 'meta-inspector' ) );
	}
}
