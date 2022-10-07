<?php
/**
 * Provides a table showing FM Term Meta data for sites
 * that have this legacy type of Fieldmanager meta data
 * (prior to term meta existing in WP Core).
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Inspect meta for terms.
 */
class Fm_Term_Meta extends Term {
	use Singleton;

	/**
	 * Object type.
	 *
	 * @var string
	 */
	public $type = 'fm-term-meta';
}
