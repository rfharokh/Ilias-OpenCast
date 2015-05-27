<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2009 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/class.ilOpenCastPlugin.php');
include_once('./Services/Repository/classes/class.ilObjectPluginListGUI.php');

/**
 * @author Timon Amstutz  <timon.amstutz@ilub.unibe.ch>
 *
 * @version       1.0.0
 */
class openCastListGUI extends ilObjectPluginListGUI {

    /**
     * @var ilOpenCastPlugin
     */
    public $plugin;

    /**
     * @var proposedOpenCastListGUI
     */
    protected $openCastList = null;

    public function __construct(){
        global $ilCtrl, $lng;

        $this->openCastList = new proposedOpenCastListGUI();
        $this->openCastList->init(
            ilOpenCastPlugin::XOCT,
            $this->plugin,
            $ilCtrl,
            $lng,
            xoctRequest::getInstanceForOpenCastObjectId($this->obj_id),
            ilObjOpenCastGUI::CMD_SEND_FILE,
            ilObjOpenCastAccess::hasAccessToDownload($this->ref_id));
    }
    public function initType() {

        $this->setType($this->openCastList->getType());
    }


    /**
     * @return string
     */
    public function getGuiClass() {
        return $this->openCastList->getGuiClass();
    }


    /**
     * @return array
     */
    public function getCommands() {
        $this->commands = $this->initCommands();
        return parent::getCommands();
    }


    /**
     * @return array
     */
    public function initCommands() {
        $commands = $this->openCastList->getCommands();
        $this->timings_enabled = $this->openCastList->getTimingsEnabled();
        $this->subscribe_enabled = $this->openCastList->getSubscribeEnabled();
        $this->payment_enabled = $this->openCastList->getPaymentEnabled();
        $this->link_enabled = $this->openCastList->getLinkEnabled();
        $this->info_screen_enabled = $this->openCastList->getInfoScreenEnabled();
        $this->delete_enabled = $this->openCastList->getDeleteEnabled();
        $this->cut_enabled = $this->openCastList->getCutEnabled();
        $this->copy_enabled = $this->openCastList->getCopyEnabled();

        return $commands;
    }

    /**
     * @param $title
     *
     * @return bool|void
     */
    public function setTitle($title) {
        parent::setTitle($this->openCastList->getTtitle());
    }


    /**
     * Get item properties
     *
     * @return    array        array of property arrays:
     *                        "alert" (boolean) => display as an alert property (usually in red)
     *                        "property" (string) => property name
     *                        "value" (string) => property value
     */
    public function getProperties() {
        return $this->openCastList->getProperties();
    }


    /**
     * insert item title
     *
     * @overwritten
     */
    public function insertTitle() {
        $this->default_command = $this->openCastList->getDefaultCommand();
        parent::insertTitle();
    }
}
?>
