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
	 * @param bool   $hide_empty Optional flag to hide the meta box if there is no meta.
	 */
	public function render_meta_table( string $title = '', bool $hide_empty = false ) {

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

			case 'bp-group':
				$meta = (array) groups_get_groupmeta( $this->object_id, '', false );
				break;

			case 'bp-activity':
				$meta = (array) bp_activity_get_meta( $this->object_id, '', false );
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
				if ( is_serialized( $value ) ) {
					$value = maybe_unserialize( $value );
				}

				$data[] = [ $key, $value ];
			}
		}

		// Render table.
		( new Table(
			$data,
			[
				__( 'Key', 'meta-inspector' ),
				__( 'Value', 'meta-inspector' ),
			],
			$title,
			$hide_empty,
		) )->render();
	}
}
