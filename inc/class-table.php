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
		if ( ! has_action( 'admin_footer', [ __CLASS__, 'output_footer' ] ) ) {
			add_action( 'admin_footer', [ __CLASS__, 'output_footer' ] );
		}

		?>
		<div class="meta-inspector">
			<?php if ( ! empty( $this->data ) || ! $this->hide_empty ) : ?>
				<?php $this->output_title(); ?>
			<?php endif; ?>

			<?php if ( ! empty( $this->data ) ) : ?>
				<table class="meta-inspector-table meta-inspector-table--cols-<?php echo (int) count( $this->headers ); ?>">
					<?php
					$this->output_headers();
					$this->output_data();
					?>
				</table>
			<?php elseif ( ! $this->hide_empty ) : ?>
				<p><?php esc_html_e( 'No data found.', 'meta-inspector' ); ?></p>
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
	 * Calculate the max height of a pre element before it is collapsed.
	 *
	 * @return int
	 */
	public static function get_max_pre_height(): int {
		/**
		 * Filter the max height of a pre element before it is collapsed (in
		 * pixels) with a default of 400 and a minimum of 100.
		 *
		 * @param int $max_pre_height The max height of a pre element before it is collapsed.
		 */
		return max( 100, (int) apply_filters( 'meta_inspector_max_pre_height', 400 ) );
	}

	/**
	 * Output some inline CSS.
	 */
	public static function output_footer() {
		?>
		<style>
			.meta-inspector table {
				table-layout: fixed;
				text-align: left;
				width: 100%;
			}
			.meta-inspector table.meta-inspector-table--cols-2 th:first-child,
			.meta-inspector table.meta-inspector-table--cols-2 td:first-child {
				width: 33%;
			}
			.meta-inspector table tbody tr td {
				padding: 10px;
				word-wrap: break-word;
				vertical-align: top;
				position: relative;
			}
			.meta-inspector table tbody tr td:hover button {
				opacity: 1;
			}
			.meta-inspector table tbody tr td:last-child {
				background: rgba( 100, 100, 100, .15 );
				line-height: 1.5rem;
			}
			.meta-inspector pre {
				margin: 0;
				white-space: pre-wrap;
			}
			.meta-inspector pre.collapsed {
				max-height: <?php echo (int) self::get_max_pre_height(); ?>px;
				overflow: hidden;
			}
			.meta-inspector pre.collapsed::after {
				background: linear-gradient( to bottom, rgba( 255, 255, 255, 0 ), rgb(218 218 218) );
				bottom: 0;
				content: '';
				height: 100px;
				left: 0;
				position: absolute;
				right: 0;
				z-index: 1;
			}
			.meta-inspector pre.expanded {
				max-height: none;
			}
			.meta-inspector td button.copy-button {
				background: #F4F4F5;
				border-radius: 4px;
				border: 1px solid #A1A1AA;
				color: #71717A;
				opacity: 0;
				padding: 2px 5px;
				position: absolute;
				right: 5px;
				top: 5px;
				transition: all 500ms ease;
			}
			.meta-inspector td button.copy-button:hover {
				color: #3F3F46;
				cursor: pointer;
			}
			.meta-inspector td button.copy-button.copied {
				color: #22C55E;
			}
			.meta-inspector td button.copy-button:not(.copied) svg.success {
				display: none;
			}
			.meta-inspector td button.copy-button.copied svg:not(.success) {
				display: none;
			}
			.meta-inspector td button.expand-link {
				background: transparent;
				border-bottom: 1px dashed #2271b1;
				border: none;
				bottom: 10px;
				color: #2271b1;
				cursor: pointer;
				font-weight: 600;
				left: 10px;
				opacity: 0;
				position: absolute;
				text-shadow: 1px 1px 1px white;
				z-index: 2;
			}
		</style>
		<script type="text/javascript">
			document.addEventListener('DOMContentLoaded', function() {
				var copyButtons = document.querySelectorAll('.meta-inspector button.copy-button')
				copyButtons.forEach(function(button) {
					button.addEventListener('click', function (event) {
						navigator.clipboard.writeText(this.parentNode.innerText);
						this.classList.add('copied');

						var previousLabel = this.getAttribute('aria-label');
						this.setAttribute('aria-label', <?php echo wp_json_encode( esc_attr__( 'Copied', 'meta-inspector' ) ); ?>);

						setTimeout(function() {
							button.classList.remove('copied');
							button.setAttribute('aria-label', previousLabel);
						}, 3000);
					});
				});

				var maxPreHeight = <?php echo (int) self::get_max_pre_height(); ?>;

				// Collapse pre elements that are taller than the max height.
				document.querySelectorAll('.meta-inspector pre').forEach(function(pre) {
					var expandLink = pre.parentNode.querySelector('.expand-link');

					if (pre.offsetHeight < maxPreHeight) {
						// Remove the expand link if the pre element is not tall enough.
						if (expandLink) {
							expandLink.parentNode.removeChild(expandLink);
						}

						return;
					}

					if (expandLink) {
						expandLink.addEventListener('click', function() {
							pre.classList.remove('collapsed');
							pre.classList.add('expanded');
							expandLink.style.display = 'none';
						});
					}

					pre.classList.add('collapsed');

					pre.addEventListener('click', function(event) {
						if (this.classList.contains('collapsed')) {
							this.classList.remove('collapsed');
							this.classList.add('expanded');
						} else {
							this.classList.remove('expanded');
							this.classList.add('collapsed');

							if (expandLink) {
								expandLink.style.display = '';
							}
						}
					});
				});
			});
		</script>
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
		$copy_button_label = esc_attr__( 'Copy to clipboard', 'meta-inspector' );
		$copy_button       = <<<HTML
			<button type="button" class="copy-button" aria-label="{$copy_button_label}">
				<svg width="20" height="20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--! Font Awesome Free 6.4.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) Copyright 2023 Fonticons, Inc. --><path d="M448 384H256c-35.3 0-64-28.7-64-64V64c0-35.3 28.7-64 64-64H396.1c12.7 0 24.9 5.1 33.9 14.1l67.9 67.9c9 9 14.1 21.2 14.1 33.9V320c0 35.3-28.7 64-64 64zM64 128h96v48H64c-8.8 0-16 7.2-16 16V448c0 8.8 7.2 16 16 16H256c8.8 0 16-7.2 16-16V416h48v32c0 35.3-28.7 64-64 64H64c-35.3 0-64-28.7-64-64V192c0-35.3 28.7-64 64-64z"/></svg>
				<svg class="success" width="20" height="20" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!-- Font Awesome Free 5.15.4 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free (Icons: CC BY 4.0, Fonts: SIL OFL 1.1, Code: MIT License) --><path d="M256 8C119.033 8 8 119.033 8 256s111.033 248 248 248 248-111.033 248-248S392.967 8 256 8zm0 48c110.532 0 200 89.451 200 200 0 110.532-89.451 200-200 200-110.532 0-200-89.451-200-200 0-110.532 89.451-200 200-200m140.204 130.267l-22.536-22.718c-4.667-4.705-12.265-4.736-16.97-.068L215.346 303.697l-59.792-60.277c-4.667-4.705-12.265-4.736-16.97-.069l-22.719 22.536c-4.705 4.667-4.736 12.265-.068 16.971l90.781 91.516c4.667 4.705 12.265 4.736 16.97.068l172.589-171.204c4.704-4.668 4.734-12.266.067-16.971z"></path></svg>
			</button>
		HTML;

		$expand_button = '<button type="button" class="expand-link" aria-label="' . esc_attr__( 'Expand', 'meta-inspector' ) . '">' . esc_html__( 'Expand', 'meta-inspector' ) . '</button>';

		if ( is_string( $value ) && ! is_numeric( $value ) ) {
			// Try to decode JSON and pretty-print it.
			$json = json_decode( $value, true );

			if ( json_last_error() === JSON_ERROR_NONE ) {
				return $copy_button . $expand_button . '<pre>' . esc_html( json_encode( $json, JSON_PRETTY_PRINT ) ) . '</pre>'; // phpcs:ignore WordPress.WP.AlternativeFunctions.json_encode_json_encode
			}
		}

		if ( is_scalar( $value ) ) {
			return $copy_button . esc_html( $value );
		}

		return $copy_button . $expand_button . '<pre>' . esc_html( var_export( $value, true ) ) . '</pre>'; // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_var_export
	}
}
