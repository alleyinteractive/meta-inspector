<?php
/**
 * Inspect posts.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta and terms for posts.
 */
class Post extends WP_Object {
	use Singleton;

	/**
	 * Object type, not post type.
	 *
	 * @var string
	 */
	public $type = 'post';

	/**
	 * Initialize class.
	 */
	protected function __construct() {
		add_action( 'add_meta_boxes', [ $this, 'add_meta_boxes' ] );
	}

	/**
	 * Add meta boxes to the post edit screen.
	 */
	public function add_meta_boxes() {

		// Store post id.
		$this->object_id = get_the_ID();

		// Post meta.
		add_meta_box(
			'meta-inspector-post-meta',
			__( 'Meta', 'meta-inspector' ),
			fn () => $this->render_meta_table(),
			get_post_type()
		);

		// Post terms.
		add_meta_box(
			'meta-inspector-post-terms',
			__( 'Terms', 'meta-inspector' ),
			[ $this, 'render_terms' ],
			get_post_type()
		);
	}

	/**
	 * Render a table of post terms.
	 */
	public function render_terms() {

		// Get taxonomies for this post.
		$taxonomies = get_post_taxonomies( $this->object_id );

		if ( empty( $taxonomies ) ) {
			printf(
				'<p>%s</p>',
				esc_html__( 'No taxonomies registered for this post type.', 'meta-inspector' )
			);

			return;
		}

		// Loop through taxonomies and terms and build data array.
		foreach ( $taxonomies as $taxonomy ) {

			// Reset data for this taxonomy.
			$data = [];

			// Get all terms.
			$terms = wp_get_post_terms(
				$this->object_id,
				$taxonomy,
				[
					'hide_empty' => false,
				]
			);

			// Build data array [ id, name, slug, taxonomy ].
			foreach ( $terms as $term ) {
				$data[] = [
					$term->term_id,
					$term->name,
					$term->slug,
					$term->taxonomy,
				];
			}

			$taxonomy_object = get_taxonomy( $taxonomy );

			if ( empty( $taxonomy_object ) ) {
				continue;
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
					$taxonomy_object->label ?? ucfirst( $taxonomy ),
				),
			) )->render();
		}
	}
}
