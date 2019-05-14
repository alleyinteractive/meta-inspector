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
	public function setup() {
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
			[ $this, 'render_meta' ],
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
	 * Render a table of post meta.
	 */
	public function render_meta() {
		$this->render_meta_table();
	}

	/**
	 * Render a table of post terms.
	 */
	public function render_terms() {

		// Get taxonomies for this post.
		$taxonomies = get_post_taxonomies( $this->object_id );

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

			new Table(
				[
					'data'    => $data,
					'headers' => [
						esc_html__( 'ID', 'meta-inspector' ),
						esc_html__( 'Name', 'meta-inspector' ),
						esc_html__( 'Slug', 'meta-inspector' ),
						esc_html__( 'Taxonomy', 'meta-inspector' ),
					],
					'title'   => $taxonomy,
				]
			);
		}
	}
}
