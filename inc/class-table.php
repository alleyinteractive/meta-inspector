<?php
/**
 * Class to generate a table of data.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Table.
 */
class Table {

	/**
	 * Table title.
	 *
	 * @var string
	 */
	public $title = '';

	/**
	 * Table headers.
	 *
	 * @var array
	 */
	public $headers = [];

	/**
	 * Table data.
	 *
	 * @var array
	 */
	public $data = [];

	/**
	 * Determine if the CSS has already been output.
	 *
	 * @var boolean
	 */
	public static $css_has_output = false;

	/**
	 * Flag to hide the table if there is no data.
	 *
	 * @var boolean
	 */
	public $hide_empty = false;

	/**
	 * Initialize a new instance of this class.
	 *
	 * @param array $args {
	 *        Optional. Arguments for the table. Default empty array.
	 *
	 *        @type string $title   Table title.
	 *        @type array  $headers Table headers.
	 *        @type array  $data    Table data.
	 * }
	 * @param bool  $render Render table immediately.
	 * @param bool  $hide_empty Hide table if there is no data.
	 */
	public function __construct( array $args = [], bool $render = true, bool $hide_empty = false ) {

		// Parse args from constructor.
		$args = wp_parse_args(
			$args,
			[
				'data'    => [],
				'headers' => [],
				'title'   => '',
			]
		);

		// Store data.
		$this->data    = (array) $args['data'];
		$this->headers = (array) $args['headers'];
		$this->title   = (string) $args['title'];

		$this->hide_empty = $hide_empty;

		// Render by default.
		if ( $render ) {
			$this->render();
		}
	}

	/**
	 * Output the current state of the table.
	 */
	public function render() {

		// Render the CSS in the footer.
		if ( ! self::$css_has_output ) {
			add_action( 'admin_footer', [ $this, 'output_css' ] );
		}

		?>
		<div class="meta-inspector">
			<?php if ( ! empty( $this->data ) || ! $this->hide_empty ) : ?>
				<?php $this->output_title(); ?>
			<?php endif; ?>

			<?php if ( ! empty( $this->data ) ) : ?>
				<table>
					<?php
					$this->output_headers();
					$this->output_data();
					?>
				</table>
			<?php elseif ( ! $this->hide_empty ) : ?>
				<p><?php esc_html_e( 'No data found', 'meta-inspector' ); ?></p>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Output the table title.
	 */
	public function output_title() {
		if ( ! empty( $this->title ) ) {
			printf(
				'<h3>%1$s</h3>',
				esc_html( $this->title )
			);
		}
	}

	/**
	 * Output the table head.
	 */
	public function output_headers() {

		// Validate headers.
		if ( empty( $this->headers ) ) {
			return;
		}

		?>
		<thead>
			<tr>
				<?php
				array_map(
					function( $header ) {
						printf(
							'<th>%1$s</th>',
							esc_html( $header )
						);
					},
					$this->headers
				);
				?>
			</tr>
		</thead>
		<?php
	}

	/**
	 * Output the table body.
	 */
	public function output_data() {

		// Validate data.
		if ( empty( $this->data ) ) {
			return;
		}

		?>
		<tbody>
			<?php
			array_map(
				function( $row ) {
					echo '<tr>';
					array_map(
						function ( $data ) use ( $row ) {
							printf(
								'<td contenteditable="%1$s">%2$s</td>',
								apply_filters( 'meta_inspector_editable_data_row', true, $row ) ? 'true' : 'false',
								is_scalar( $data ) ? esc_html( $data ) : '<pre>' . esc_html( var_export( $data, true ) ) . '</pre>' // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export, WordPress.Security.EscapeOutput.OutputNotEscaped
							);
						},
						$row
					);
				},
				$this->data
			)
			?>
		</tbody>
		<?php
	}

	/**
	 * Output some inline CSS.
	 */
	public function output_css() {
		echo '
		<style>
			.meta-inspector table {
				table-layout: fixed;
				text-align: left;
				width: 100%;
			}
			.meta-inspector table thead tr td:first-child {
				width: 25%;
			}
			.meta-inspector table thead tr td:last-child {
				width: 70%;
			}
			.meta-inspector table tbody tr td {
				padding-bottom: .5rem;
			}
			.meta-inspector table tbody tr td:first-child {
				word-wrap: break-word;
			}
			.meta-inspector table tbody tr td:last-child {
				background: rgba( 100, 100, 100, .15 );
				line-height: 1.5rem;
				padding: 10px;
				word-wrap: break-word;
			}
		</style>
		';
	}
}
