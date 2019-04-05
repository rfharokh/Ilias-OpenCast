<?php

namespace srag\CustomInputGUIs\OpenCast;

/**
 * Trait CustomInputGUIsTrait
 *
 * @package srag\CustomInputGUIs\OpenCast
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
trait CustomInputGUIsTrait {

	/**
	 * @return CustomInputGUIs
	 */
	protected static final function customInputGUIs() {
		return CustomInputGUIs::getInstance();
	}
}
