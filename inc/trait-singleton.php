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
	 * Class instance.
	 *
	 * @var null|self
	 */
	protected static $instance;

	/**
	 * Get class instance
	 *
	 * @return self
	 */
	public static function instance() {
		if ( ! isset( static::$instance ) ) {
			static::$instance = new static();
			static::$instance->setup();
		}
		return static::$instance;
	}

	/**
	 * Setup the singleton.
	 */
	public function setup() {
		// Silence.
	}
}
