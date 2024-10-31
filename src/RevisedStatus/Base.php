<?php
namespace RevisedStatus;

/**
 * Class Base
 * @package RevisedStatus
 */
trait Base {

	/**
	 * @var $this
	 */
	static $instance;

	/**
	 * @return self::$instance
	 */
	static function getInstance() {

		if ( self::$instance != null ) {
			return self::$instance;
		} else {
			return self::$instance = new self();
		}

	}

}