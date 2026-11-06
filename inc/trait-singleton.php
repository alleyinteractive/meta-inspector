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
	 * @var array<class-string, static>
	 */
	protected static $instances = [];

	/**
	 * Get class instance.
	 *
	 * @return static
	 */
	public static function instance(): static {
		return static::$instances[ static::class ] ??= new static();
	}
}
