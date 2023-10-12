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
class Fm_Term_Meta extends Term {
	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'fm-term-meta';

	/**
	 * Render a table of post meta.
	 */
	public function add_meta_boxes() {
		if ( ! function_exists( 'fm_get_term_meta' ) ) {
			return;
		}

		// Ensure the term_id is set.
		if ( ! isset( $_GET['tag_ID'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		// Store term id.
		$this->object_id = absint( $_GET['tag_ID'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		// Render table of meta data.
		$this->render_meta_table( __( 'Fieldmanager Term Meta', 'meta-inspector' ), true );
	}
}
