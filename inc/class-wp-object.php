<?php
/**
 * Class to inspect an object.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Abstract object class.
 *
 * @phpstan-consistent-constructor
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
	 * Constructor.
	 */
	protected function __construct() {}

	/**
	 * Helper to automagically render a meta table for the current type and id.
	 *
	 * @param string $title Optional table title.
	 * @param bool   $hide_empty Optional flag to hide the meta box if there is no meta.
	 */
	public function render_meta_table( string $title = '', bool $hide_empty = false ): void {

		// Store meta.
		$meta = [];

		switch ( $this->type ) {
			case 'post':
				$meta = (array) get_post_meta( $this->object_id );
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
					if ( ! $term instanceof \WP_Term ) {
						return;
					}

					$meta = (array) fm_get_term_meta( $this->object_id, $term->taxonomy );
					if ( empty( $meta ) ) {
						// Do not display FM Term Meta table on terms without this meta.
						return;
					}
				}
				break;
		}

		/**
		 * Filter to sort the meta keys.
		 *
		 * @return bool Whether to sort the meta keys. Default true.
		 */
		if ( apply_filters( 'meta_inspector_sort_meta', true ) ) {
			ksort( $meta );
		}

		// Build data array [ key, value ].
		$data = [];
		foreach ( $meta as $key => $values ) {
			foreach ( (array) $values as $value ) {
				if ( is_string( $value ) && is_serialized( $value ) ) {
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

	/**
	 * Read a numeric object ID from the current request's query string.
	 *
	 * @param string $key Query string key.
	 * @return int The ID, or 0 when it is missing or not numeric.
	 */
	protected function get_id_from_request( string $key ): int {
		$value = $_GET[ $key ] ?? 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash

		return is_numeric( $value ) ? absint( $value ) : 0;
	}
}
