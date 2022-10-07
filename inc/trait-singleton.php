<?php
/**
 * Easily turn any class into a singleton.
 *
 * @package Meta_Inspector
 */

namespace Meta_Inspector;

/**
 * Singleton trait.
 */
trait Singleton {
	/**
	 * Existing instances.
	 *
	 * @var array
	 */
	protected static $instances = [];

	/**
	 * Get class instance.
	 *
	 * @return static
	 */
	public static function instance() {
		$class = get_called_class();

		if ( ! isset( static::$instances[ $class ] ) ) {
			static::$instances[ $class ] = new static();
		}

		return self::$instances[ $class ];
	}
}
