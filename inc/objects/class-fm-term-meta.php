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
	public function add_meta_boxes(): void {
		if ( ! function_exists( 'fm_get_term_meta' ) ) {
			return;
		}

		$this->object_id = $this->get_id_from_request( 'tag_ID' );

		if ( ! $this->object_id ) {
			return;
		}

		// Render table of meta data.
		$this->render_meta_table( __( 'Fieldmanager Term Meta', 'meta-inspector' ), true );
	}
}
