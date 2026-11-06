<?php
/**
 * Inspect BuddyPress groups.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta and terms for BuddyPress groups.
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

		// Bail if the BuddyPress groups component is not active.
		if ( ! bp_is_active( 'groups' ) ) {
			return;
		}

		add_action( 'bp_groups_admin_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add meta boxes to the BuddyPress group edit screen.
	 */
	public function add_meta_boxes(): void {

		$this->object_id = $this->get_id_from_request( 'gid' );
		$screen          = get_current_screen();

		if ( ! $this->object_id || ! $screen ) {
			return;
		}

		$screen_id = $screen->id;

		// Group meta.
		add_meta_box(
			'meta-inspector-bp-group-meta',
			__( 'Meta', 'meta-inspector' ),
			fn () => $this->render_meta_table(),
			$screen_id,
			'normal'
		);

		// Group terms.
		add_meta_box(
			'meta-inspector-bp-group-terms',
			__( 'Terms', 'meta-inspector' ),
			[ $this, 'render_terms' ],
			$screen_id,
			'normal'
		);
	}

	/**
	 * Render a table of group terms.
	 */
	public function render_terms(): void {

		// Get group taxonomies.
		$taxonomies = get_object_taxonomies( 'bp_group', 'objects' );

		if ( empty( $taxonomies ) ) {
			printf(
				'<p>%s</p>',
				esc_html__( 'No taxonomies registered for this group.', 'meta-inspector' )
			);

			return;
		}

		// Loop through taxonomies and terms and build data array.
		foreach ( $taxonomies as $taxonomy ) {

			$taxonomy_object = get_taxonomy( $taxonomy->name );

			if ( empty( $taxonomy_object ) ) {
				continue;
			}

			// Reset data for this taxonomy.
			$data = [];

			// Get all terms.
			$terms = bp_get_object_terms(
				$this->object_id,
				$taxonomy->name,
				[ 'hide_empty' => false ]
			);

			// Build data array [ id, name, slug, taxonomy ].
			foreach ( $terms as $term ) {
				if ( ! $term instanceof \WP_Term ) {
					continue;
				}

				// Get singular name if available.
				$singular_name = get_term_meta( $term->term_id, 'bp_type_singular_name', true );
				$term_name     = is_string( $singular_name ) && '' !== $singular_name ? $singular_name : $term->name;

				$data[] = [
					$term->term_id,
					$term_name,
					$term->slug,
					$term->taxonomy,
				];
			}

			( new Table(
				$data,
				[
					__( 'ID', 'meta-inspector' ),
					__( 'Name', 'meta-inspector' ),
					__( 'Slug', 'meta-inspector' ),
					__( 'Taxonomy', 'meta-inspector' ),
				],
				sprintf(
					/* translators: %s: taxonomy name */
					__( 'Taxonomy: %s', 'meta-inspector' ),
					$taxonomy_object->label ?: ucfirst( $taxonomy->name ),
				),
			) )->render();
		}
	}
}
