<?php
/**
 * Class to generate a table of data.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Table to Display Data
 */
class Table {
	/**
	 * Initialize a new instance of this class.
	 *
	 * @param array  $data       Table data.
	 * @param array  $headers    Table headers.
	 * @param string $title      Table title.
	 * @param bool   $hide_empty Optional flag to hide the table if there is no data.
	 */
	public function __construct(
		public array $data,
		public array $headers,
		public string $title,
		public bool $hide_empty = false,
	) {
	}

	/**
	 * Output the current state of the table.
	 */
	public function render() {

		// Render the CSS in the footer.
		if ( ! has_action( 'admin_footer', [ __CLASS__, 'output_css' ] ) ) {
			add_action( 'admin_footer', [ __CLASS__, 'output_css' ] );
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
			printf( '<h3>%s</h3>', esc_html( $this->title ) );
		}
	}

	/**
	 * Output the table head.
	 */
	public function output_headers() {
		if ( empty( $this->headers ) ) {
			return;
		}

		?>
		<thead>
			<tr>
				<?php
				array_map(
					fn ( $header ) => printf( '<th>%1$s</th>', esc_html( $header ) ),
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
						fn ( $data ) => printf( '<td>%s</td>', $this->format_value_for_output( $data ) ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						$row,
					);
				},
				$this->data,
			)
			?>
		</tbody>
		<?php
	}

	/**
	 * Output some inline CSS.
	 */
	public static function output_css() {
		?>
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
				width: 75%;
			}
			.meta-inspector table tbody tr td {
				padding: 10px;
				word-wrap: break-word;
				user-select: all;
				vertical-align: top;
			}
			.meta-inspector table tbody tr td:last-child {
				background: rgba( 100, 100, 100, .15 );
				line-height: 1.5rem;
			}
			.meta-inspector pre {
				overflow-y: scroll;
				margin: -5px -10px -5px 0;
			}
		</style>
		<?php
	}

	/**
	 * Output a formatted cell value.
	 *
	 * For scalar values, this will use var_export() to improve readability. For
	 * JSON, this will use json_encode() to improve readability.
	 *
	 * @param mixed $value Cell value.
	 * @return string
	 */
	protected function format_value_for_output( $value ): string {
		if ( is_string( $value ) ) {
			// Try to decode JSON and pretty-print it.
			$json = json_decode( $value, true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				return '<pre>' . esc_html( json_encode( $json, JSON_PRETTY_PRINT ) ) . '</pre>'; // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			}
		}

		if ( is_scalar( $value ) ) {
			return esc_html( $value );
		}

		return '<pre>' . esc_html( var_export( $value, true ) ) . '</pre>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
	}
}
