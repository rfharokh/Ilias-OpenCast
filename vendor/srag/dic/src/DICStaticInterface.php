<?php

namespace srag\DIC\OpenCast;

use srag\DIC\OpenCast\DIC\DICInterface;
use srag\DIC\OpenCast\Exception\DICException;
use srag\DIC\OpenCast\Output\OutputInterface;
use srag\DIC\OpenCast\Plugin\PluginInterface;
use srag\DIC\OpenCast\Version\VersionInterface;

/**
 * Interface DICStaticInterface
 *
 * @package srag\DIC\OpenCast
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface DICStaticInterface {

	/**
	 * Clear cache. Needed for instance in unit tests
	 *
	 * @deprecated
	 */
	public static function clearCache()/*: void*/ ;


	/**
	 * Get DIC interface
	 *
	 * @return DICInterface DIC interface
	 *
	 * @throws DICException DIC not supports ILIAS X.X.X anymore!"
	 */
	public static function dic();


	/**
	 * Get output interface
	 *
	 * @return OutputInterface Output interface
	 */
	public static function output();


	/**
	 * Get plugin interface
	 *
	 * @param string $plugin_class_name
	 *
	 * @return PluginInterface Plugin interface
	 *
	 * @throws DICException Class $plugin_class_name not exists!
	 * @throws DICException Class $plugin_class_name not extends ilPlugin!
	 * @logs   DEBUG Please implement $plugin_class_name::getInstance()!
	 */
	public static function plugin($plugin_class_name);


	/**
	 * Get version interface
	 *
	 * @return VersionInterface Version interface
	 */
	public static function version();
}
