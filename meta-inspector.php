<?php
/**
 * Plugin Name: Meta Inspector
 * Description: See post, term, and user meta values, and easily update them.
 * Author: Alley Interactive, James Burke
 * Version: 0.1
 * Author URI: http://alleyinteractive.com
 */

class Meta_Inspector {

	/**
	 * Store the instance of Meta_Inspector for the singleton.
	 * @var Meta_Inspector
	 */
	private static $instance;

	/**
	 * The type of meta being displayed. Can be `post`, `term`, or `user`.
	 * @var string
	 */
	private static $type;

	/**
	 * The ID of the object whose meta data is being rendered.
	 * @var int
	 */
	private static $object_id;

	/**
	 * Meta data array.
	 * @var array
	 */
	private static $meta_data;

	/**
	 * Singleton instance implementation.
	 * @return Meta_Inspector
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new Meta_Inspector;
			self::setup();
		}
		return self::$instance;
	}

	private function __constructor() {

	}

	/**
	 * Replacement constructor for hooking actions
	 */
	private static function setup() {

		// Ajax endpoint to update meta data
		add_action( 'wp_ajax_meta_inspector_update_meta_value', array( self::$instance, 'update_meta_value' ) );

		// Add meta inspector to posts
		add_action( 'add_meta_boxes', array( self::$instance, 'register_meta_box' ) );

		add_action( 'add_meta_boxes', function(){

		} );

		// Hook into all registered taxonomies
		add_action( 'registered_taxonomy', function( $taxonomy ) {

			// Add meta inspector to the bottom of the term edit screen
			add_action( $taxonomy . '_edit_form', array( self::$instance, 'term_meta'), 1000 );
		}, 1000 );

		// Add meta inspector to users
		add_action( 'edit_user_profile', array( self::$instance, 'user_meta'), 1000 );
		add_action( 'show_user_profile', array( self::$instance, 'user_meta'), 1000 );
	}

	/**
	 * Register Meta box for Display
	 */
	public function register_meta_box() {
		$type = get_post_type();

		// If we can't find a post type it's likely the comments page.
		if ( empty( $type ) ) {
			$type = get_current_screen()->id;
		}

		switch ( $type ) {
			case 'comment' :
				add_meta_box(
					'meta-inspector-metabox',
					__( 'Post Meta Inspector', 'meta-inspector' ),
					array( self::$instance, 'comment_meta' ),
					$type,
					'normal'
				);
				break;

			default:
				add_meta_box(
					'meta-inspector-metabox',
					__( 'Post Meta Inspector', 'meta-inspector' ),
					array( self::$instance, 'post_meta' ),
					$type
				);
				break;
		}
	}

	/**
	 * Update any meta value via ajax
	 */
	public function update_meta_value() {

		// Store errors
		$errors = array();

		// All the data being passed into this call
		$data = array(
			'key' => 'key',
			'type' => 'type',
			'object_id' => 'objectID',
			'original_value' => 'originalValue',
			'new_value' => 'newValue',
			'nonce' => 'nonce',
		);

		// Loop through $data to validate the $_POST values
		foreach ( $data as $php_key => $js_key ) {

			// Does the $_POST value exist?
			if ( isset( $_POST[ $js_key ] ) ) {

				//
				$$php_key = sanitize_text_field( wp_unslash( $_POST[ $js_key ] ) );
			} else {
				$errors[ $js_key ] = "Invalid {$js_key}";
			}
		}

		// Verify nonce
		if ( ! check_ajax_referer( 'update-meta-' . $type, 'nonce', false ) ) {
			$errors['nonce'] = 'Invalid NONCE';
		}

		// Send errors
		if ( ! empty( $errors ) ) {
			wp_send_json_error( $errors );
			exit();
		}

		// Determine which type of meta to update
		switch ( $type ) {
			case 'post' :
				$updated_meta = update_post_meta( $object_id, $key, $new_value, $original_value );
				break;

			case 'term' :
				$updated_meta = update_term_meta( $object_id, $key, $new_value, $original_value );
				break;

			case 'user' :
				$updated_meta = update_user_meta( $object_id, $key, $new_value, $original_value );
				break;
		}

		// Did meta data successfully update?
		if ( true === $updated_meta ) {

			// Send success message
			wp_send_json_success( array(
				'newValue' => $new_value,
			) );
		} else {
			wp_send_json_error( __( 'Meta data failed to update.', 'meta-inspector' ) );
		}

		exit();
	}

	/**
	 * Get post meta and generate a table
	 */
	public function post_meta() {

		// Setup class for a post
		Meta_Inspector::$object_id = get_the_ID();
		Meta_Inspector::$type = 'post';
		Meta_Inspector::$meta_data = get_post_meta( Meta_Inspector::$object_id );

		// Generate table
		$this->generate_meta_table();
	}

	/**
	 * Get term meta and generate a table
	 */
	public function term_meta() {

		// Ensure the term_id is set
		if ( ! isset( $_GET['tag_ID'] ) ) {
			return;
		}

		// Setup class for a post
		Meta_Inspector::$type = 'term';
		Meta_Inspector::$object_id = absint( $_GET['tag_ID' ] );
		Meta_Inspector::$meta_data = get_term_meta( Meta_Inspector::$object_id );

		// Generate table
		$this->generate_meta_table();
	}

	/**
	 * Get comment meta and generate the table
	 */
	public function comment_meta() {

		Meta_Inspector::$type = 'comment';
		Meta_Inspector::$object_id = get_comment_ID();
		Meta_Inspector::$meta_data = get_comment_meta( Meta_Inspector::$object_id );

		$this->generate_meta_table();
	}

	/**
	 * Get user meta and generate a table
	 */
	public function user_meta() {

		// Set $this->object_id to the user's ID
		if ( defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE ) {
			Meta_Inspector::$object_id = get_current_user_id();

		} elseif ( isset( $_GET['user_id'] ) ) {
			Meta_Inspector::$object_id = absint( $_GET['user_id' ] );

		} else {
			return;
		}

		// Setup class for a post
		Meta_Inspector::$type = 'user';
		Meta_Inspector::$meta_data = get_user_meta( Meta_Inspector::$object_id );

		// Generate table
		$this->generate_meta_table();
	}

	/**
	 * Output all the necessary output for the meta table
	 */
	public function generate_meta_table() {

		// Ensure that meta data actually exists
		if ( empty( Meta_Inspector::$meta_data ) && ! is_array( Meta_Inspector::$meta_data ) ) {
			return;
		}

		// Generate a title if needed
		switch ( Meta_Inspector::$type ) {
			case 'user' :
				$title = __( 'User Meta', 'meta-inspector' );
				break;

			case 'term' :
				$title = __( 'Term Meta', 'meta-inspector' );
				break;
		}
		?>

		<style>
			#meta-inspector table {
				table-layout: fixed;
				text-align: left;
				width: 100%;
			}
			#meta-inspector table thead tr td:first-child {
				width: 25%;
			}
			#meta-inspector table thead tr td:last-child {
				width: 70%;
			}
			#meta-inspector table tbody tr td {
				padding-bottom: .5rem;
			}
			#meta-inspector table tbody tr td:first-child {
				word-wrap: break-word;
			}
			#meta-inspector table tbody tr td:last-child {
				background: rgba( 100, 100, 100, .15 );
				line-height: 1.5rem;
				padding: 10px;
				word-wrap: break-word;
			}
		</style>

		<div
			id="meta-inspector"
			data-type="<?php echo esc_attr( Meta_Inspector::$type ); ?>"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'update-meta-' . Meta_Inspector::$type ) ); ?>"
			data-object-id="<?php echo esc_attr( Meta_Inspector::$object_id ) ?>"
		>
			<?php

			// Output title if needed
			if ( ! empty( $title ) ) {
				echo '<h3>' . esc_html( $title ) . '</h3>';
			}
			?>

			<table>
				<thead>
					<tr>
						<th><?php esc_html_e( 'Key', 'meta-inspector' ); ?></th>
						<th><?php esc_html_e( 'Value', 'meta-inspector' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					// Loop through all meta keys
					foreach ( Meta_Inspector::$meta_data as $key => $values ) {

						// Loop through values
						foreach ( $values as $value ) {

							// Prep value as a readable string, and trim surrounding quotes
							$value = substr( var_export( $value, true ), 1, -1 );

							// Output table row
							?>
								<tr>
									<td><?php echo esc_html( $key ); ?></td>
									<td
										class="meta-value"
										contenteditable="true"
										data-key="<?php echo esc_attr( $key ); ?>"
										data-original-value="<?php echo esc_attr( $value ); ?>"
									><?php echo esc_html( $value ); ?></td>
								</tr>

							<?php
						}
					}
					?>
				</tbody>
			</table>
		</div>

		<script>
		jQuery(document).ready(function() {

			// Turn values into textarea boxes
			jQuery('#meta-inspector table .meta-value').on('blur', function(){

				// Capture current value on click
				var metaField = jQuery(this);
				var key = metaField.data('key');
				var originalValue = metaField.data('original-value');

				// Get some meta values to update values properly
				var wrapperDiv = jQuery('#meta-inspector');
				var type = wrapperDiv.data('type');
				var nonce = wrapperDiv.data('nonce');
				var objectID = wrapperDiv.data('object-id');

				// Get newValue
				var newValue = metaField.text();

				// Only save if values are different
				if ( newValue.toString() !== originalValue.toString() ) {

					// Build data
					var data = {
						action: 'meta_inspector_update_meta_value',
						key: key,
						type: type,
						objectID, objectID,
						originalValue: originalValue,
						newValue: newValue,
						nonce: nonce,
					};

					// Indicate to the user that the field is saving
					metaField.text('Saving...');

					// Execute ajax save
					jQuery.ajax({
						type: 'POST',
						url: ajaxurl,
						data: data,
						success: function(data){
							if ( true === data.success ) {

								// Update field to the new value
								metaField.text(data.data.newValue);

								// Store the new value as the original value
								metaField.data('original-value', data.data.newValue);
							} else {

								// Display error message
								metaField.text('Could not save meta data...');

								// Update to the original value after 2 seconds
								window.setTimeout(function () {
									metaField.text(originalValue);
								}, 2000);
							}
						},
						error: function(data){
							// Display error message
							metaField.text('Could not save meta data...');

							// Update to the original value after 2 seconds
							window.setTimeout(function () {
								metaField.text(originalValue);
							}, 2000);
						}
					});
				}
			});
		});
		</script>
		<?php
	}
}

/**
 * Return an instance of Meta_Inspector
 */
function Meta_Inspector() {
	return Meta_Inspector::instance();
}
add_action( 'plugins_loaded', 'Meta_Inspector' );
