<?php
/**
 * Class to inspect an object.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Abstract object class.
 */
abstract class WP_Object {

	/**
	 * Object type.
	 *
	 * @var string
	 */
	protected $type;

	/**
	 * Object ID.
	 *
	 * @var int
	 */
	protected $object_id;

	/**
	 * Helper to automagically render a meta table for the current type and id.
	 *
	 * @param string $title Optional table title.
	 */
	public function render_meta_table( string $title = '' ) {

		// Store meta.
		$meta = [];

		switch ( $this->type ) {
			case 'post':
				$meta = get_post_meta( $this->object_id );
				break;

			case 'term':
				$meta = (array) get_term_meta( $this->object_id );
				break;

			case 'user':
				$meta = (array) get_user_meta( $this->object_id );
				break;

			case 'comment':
				$meta = (array) get_comment_meta( $this->object_id );
				break;

			case 'fm-term-meta':
				if ( function_exists( 'fm_get_term_meta' ) ) {
					$term = get_term( $this->object_id );
					$meta = (array) fm_get_term_meta( $this->object_id, $term->taxonomy );
					if ( empty( $meta ) ) {
						// Do not display FM Term Meta table on terms without this meta.
						return;
					}
				}
				break;
		}

		// Build data array [ key, value ].
		$data = [];
		foreach ( $meta as $key => $values ) {
			foreach ( $values as $value ) {
				$data[] = [
					$key,
					substr( var_export( $value, true ), 1, -1 ),
				];
			}
		}

		// Render table.
		new Table(
			[
				'data'    => $data,
				'headers' => [
					esc_html__( 'Key', 'meta-inspector' ),
					esc_html__( 'Value', 'meta-inspector' ),
				],
				'title'   => $title,
			]
		);
	}
}
