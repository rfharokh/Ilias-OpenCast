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
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/classes/Request/class.xoctRequest.php');
include_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/tests/openCastListGUI/interface.openCastListGUIInterface.php');

/**
 * Goal: Make dependencies to ILIAS small and transparent and transform the class to testable unit.
 *
 * Remark: Most annotations and remarks should be viewed as proposals. The author is aware, that different opinions might very well exist.
 *
 * @author Timon Amstutz  <timon.amstutz@ilub.unibe.ch>
 *
 */
class proposedOpenCastListGUI implements openCastListGUIInterface {

    /**
     * @var ilRepositoryObjectPlugin
     */
    public $plugin;

    /**
     * @var string
     */
    protected $type = "";

    /**
     * @var string
     */
    protected $title = "";

    /**
     * @var xoctRequest
     */
    protected $xoctRequest = null;

    /**
     * @var ilCtrl $ilCtrl
     */
    protected $ilCtrl = null;

    /**
     * @var bool
     */
    protected $timings_enabled = false;

    /**
     * @var bool
     */
    protected $subscribe_enabled = false;

    /**
     * @var bool
     */
    protected $payment_enabled = false;

    /**
     * @var bool
     */
    protected $link_enabled = false;

    /**
     * @var bool
     */
    protected $info_screen_enabled = true;

    /**
     * @var bool
     */
    protected $delete_enabled = false;

    /**
     * @var bool
     */
    protected $cut_enabled = false;

    /**
     * @var bool
     */
    protected $copy_enabled = false;

    /**
     * @var array
     */
    protected $default_command = null;

    /**
     * @var bool
    */
    protected $has_access_to_download = false;

    /**
     * @var string
     */
    protected $cmd_send_file = "";

    /**
     * @var ilLanguage
     */
    protected $lng = null;

    /**
     * @var
     */
    /**
     *
     */
    public function __construct(){}

    /**
     * @param $type
     * @param ilPlugin $plugin
     * @param ilCtrl $ilCtrl
     * @param ilLanguage $lng
     * @param xoctRequest $request
     * @param string $cmd_send_file
     * @param bool $has_access_to_download
     * @return mixed|void
     */
    public function init($type,ilPlugin $plugin, ilCtrl $ilCtrl, ilLanguage $lng, xoctRequest $request, $cmd_send_file = "", $has_access_to_download = false){
        $this->setPlugin($plugin);
        $this->setType($type);
        $this->setXoctRequest($request);
        $this->setHasAccessToDownload($has_access_to_download);
        $this->setIlCtrl($ilCtrl);
        $this->setLng($lng);
        $this->setCmdSendFile($cmd_send_file);
    }

    /**
     * @param \ilRepositoryObjectPlugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return \ilRepositoryObjectPlugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param \ilCtrl $ilCtrl
     */
    public function setIlCtrl($ilCtrl)
    {
        $this->ilCtrl = $ilCtrl;
    }

    /**
     * @return \ilCtrl
     */
    public function getIlCtrl()
    {
        return $this->ilCtrl;
    }

    /**
     * @param \ilLanguage $lng
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
    }

    /**
     * @return \ilLanguage
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \xoctRequest $xoctRequest
     */
    public function setXoctRequest($xoctRequest)
    {
        $this->xoctRequest = $xoctRequest;
    }

    /**
     * @return \xoctRequest
     */
    public function getXoctRequest()
    {
        return $this->xoctRequest;
    }

    /**
     * @param boolean $copy_enabled
     */
    public function setCopyEnabled($copy_enabled)
    {
        $this->copy_enabled = $copy_enabled;
    }

    /**
     * @return boolean
     */
    public function getCopyEnabled()
    {
        return $this->copy_enabled;
    }

    /**
     * @param boolean $cut_enabled
     */
    public function setCutEnabled($cut_enabled)
    {
        $this->cut_enabled = $cut_enabled;
    }

    /**
     * @return boolean
     */
    public function getCutEnabled()
    {
        return $this->cut_enabled;
    }

    /**
     * @param boolean $delete_enabled
     */
    public function setDeleteEnabled($delete_enabled)
    {
        $this->delete_enabled = $delete_enabled;
    }

    /**
     * @return boolean
     */
    public function getDeleteEnabled()
    {
        return $this->delete_enabled;
    }

    /**
     * @param boolean $info_screen_enabled
     */
    public function setInfoScreenEnabled($info_screen_enabled)
    {
        $this->info_screen_enabled = $info_screen_enabled;
    }

    /**
     * @return boolean
     */
    public function getInfoScreenEnabled()
    {
        return $this->info_screen_enabled;
    }

    /**
     * @param boolean $link_enabled
     */
    public function setLinkEnabled($link_enabled)
    {
        $this->link_enabled = $link_enabled;
    }

    /**
     * @return boolean
     */
    public function getLinkEnabled()
    {
        return $this->link_enabled;
    }

    /**
     * @param boolean $payment_enabled
     */
    public function setPaymentEnabled($payment_enabled)
    {
        $this->payment_enabled = $payment_enabled;
    }

    /**
     * @return boolean
     */
    public function getPaymentEnabled()
    {
        return $this->payment_enabled;
    }

    /**
     * @param boolean $subscribe_enabled
     */
    public function setSubscribeEnabled($subscribe_enabled)
    {
        $this->subscribe_enabled = $subscribe_enabled;
    }

    /**
     * @return boolean
     */
    public function getSubscribeEnabled()
    {
        return $this->subscribe_enabled;
    }

    /**
     * @param boolean $timings_enabled
     */
    public function setTimingsEnabled($timings_enabled)
    {
        $this->timings_enabled = $timings_enabled;
    }

    /**
     * @return boolean
     */
    public function getTimingsEnabled()
    {
        return $this->timings_enabled;
    }

    /**
     * @param array $default_command
     */
    public function setDefaultCommand($default_command)
    {
        $this->default_command = $default_command;
    }

    /**
     * @param string $cmd_send_file
     */
    public function setCmdSendFile($cmd_send_file)
    {
        $this->cmd_send_file = $cmd_send_file;
    }

    /**
     * @return string
     */
    public function getCmdSendFile()
    {
        return $this->cmd_send_file;
    }


    /**
     * @return string
     */
    public function getGuiClass() {
        return 'ilObjOpenCastGUI';
    }


    /**
     * @param boolean $has_access_to_download
     */
    public function setHasAccessToDownload($has_access_to_download)
    {
        $this->has_access_to_download = $has_access_to_download;
    }

    /**
     * @return boolean
     */
    public function getHasAccessToDownload()
    {
        return $this->has_access_to_download;
    }

    /**
     * @return array
     */
    public function getCommands() {
        return $this->initCommands();
    }


    /**
     * @return array
     */
    public function initCommands() {
        // Always set
        $this->setTimingsEnabled(false);
        $this->setSubscribeEnabled(false);
        $this->setPaymentEnabled(false);
        $this->setLinkEnabled(false);
        $this->setInfoScreenEnabled(true);
        $this->setDeleteEnabled(false);

        // Should be overwritten according to status
        $this->setCutEnabled(false);;
        $this->setCopyEnabled(false);

        $commands = array(
            array(
                'permission' => 'read',
                'cmd' => 'showContent',
                'default' => true
            )
        );

        $request = $this->getXoctRequest();
        switch ($request->getStatus()) {
            case $request::STATUS_IN_PROGRRESS:
                break;
            case $request::STATUS_REFUSED:
            case $request::STATUS_COPY:

                $commands[] = array(
                    'txt' => $this->plugin->txt('common_cmd_delete'),
                    'permission' => 'delete',
                    'cmd' => 'confirmDeleteObject',
                    'default' => false
                );
                break;

            case $request::STATUS_NEW:
            case $request::STATUS_RELEASED:
                $commands[] = array(
                    'txt' => $this->plugin->txt('common_cmd_delete'),
                    'permission' => 'delete',
                    'cmd' => 'confirmDeleteObject',
                    'default' => false
                );

                $this->setCutEnabled(true);
                $this->setCopyEnabled(true);
                break;
        }
        return $commands;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }


    /**
     * @return string
     */
    public function getTitle() {
        $this->setTitle($this->getXoctRequest()->getTitle() . ' / ' . $this->getXoctRequest()->getAuthor());
        return $this->title;
    }


    /**
     * insert item title
     *
     * @overwritten
     */
    public function getDefaultCommand() {
        $request = $this->getXoctRequest();

        switch ($this->getXoctRequest()->getStatus()) {
            case $request::STATUS_NEW:
            case $request::STATUS_IN_PROGRRESS:
            case $request::STATUS_REFUSED:
                $this->setDefaultCommand(null);
                break;
            case $request::STATUS_RELEASED:
            case $request::STATUS_COPY:
                if ($this->getHasAccessToDownload()) {
                    $this->getIlCtrl()->setParameterByClass('ilObjOpenCastGUI', $request::XDGL_ID, $request->getId());
                    $this->default_command = array(
                        'link' => $this->getIlCtrl()->getLinkTargetByClass('ilObjOpenCastGUI', $this->getCmdSendFile()),
                        'frame' => '_top'
                    );
                } else {
                    $this->setDefaultCommand(null);
                }

                break;
        }
        return $this->default_command;
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
        global $lng;

        $info_string = '';
        $info_string .= $this->getXoctRequest()->getTitle() . ' ';
        $info_string .= '(' . $this->getXoctRequest()->getPublishingYear() . '), ';
        // $info_string .= $this->plugin->txt('obj_list_page') . ' ';
        $info_string .= $this->getXoctRequest()->getPages();

        $props[] = array(
            'alert' => false,
            'newline' => true,
            'property' => 'description',
            'value' => $info_string,
            'propertyNameVisible' => false
        );

        $request = $this->getXoctRequest();
        switch ($this->getXoctRequest()->getStatus()) {
            case $request::STATUS_NEW:
                $props[] = array(
                    'alert' => true,
                    'newline' => true,
                    'property' => $this->getLng()->txt('status'),
                    'value' => $this->getPlugin()->txt('request_status_' . $request::STATUS_NEW),
                    'propertyNameVisible' => true
                );
                $props[] = array(
                    'alert' => false,
                    'newline' => true,
                    'property' => $this->getPlugin()->txt('request_creation_date'),
                    'value' => self::format_date_time($this->getXoctRequest()->getCreateDate()),
                    'propertyNameVisible' => true
                );
                break;
            case $request::STATUS_IN_PROGRRESS:
                $props[] = array(
                    'alert' => true,
                    'newline' => true,
                    'property' => $this->getLng()->txt('status'),
                    'value' => $this->getPlugin()->txt('request_status_' . $request::STATUS_IN_PROGRRESS),
                    'propertyNameVisible' => true
                );
                $props[] = array(
                    'alert' => false,
                    'newline' => true,
                    'property' => $this->getPlugin()->txt('request_creation_date'),
                    'value' => self::format_date_time($this->getXoctRequest()->getCreateDate()),
                    'propertyNameVisible' => true
                );
                break;

            case $request::STATUS_REFUSED:
                $props[] = array(
                    'alert' => true,
                    'newline' => true,
                    'property' => $this->getLng()->txt('status'),
                    'value' => $this->getPlugin()->txt('request_status_' . $request::STATUS_REFUSED),
                    'propertyNameVisible' => true
                );
                $props[] = array(
                    'alert' => false,
                    'newline' => true,
                    'property' => $this->getPlugin()->txt('request_creation_date'),
                    'value' => self::format_date_time($this->getXoctRequest()->getCreateDate()),
                    'propertyNameVisible' => true
                );
                $props[] = array(
                    'alert' => false,
                    'newline' => true,
                    'property' => $this->getPlugin()->txt('request_refusing_date'),
                    'value' => self::format_date_time($this->getXoctRequest()->getDateLastStatusChange()),
                    'propertyNameVisible' => true
                );
                break;

            case $request::STATUS_RELEASED:
            case $request::STATUS_COPY:
                // Display a warning if a file is not a hidden Unix file, and
                // the filename extension is missing
                $file = $this->getXoctRequest()->getAbsoluteFilePath();

                if (!preg_match('/^\\.|\\.[a-zA-Z0-9]+$/', $file)) {
                    $props[] = array(
                        'alert' => false,
                        'property' => $lng->txt('filename_interoperability'),
                        'value' => $lng->txt('filename_extension_missing'),
                        'propertyNameVisible' => false
                    );
                }
                $props[] = array(
                    'alert' => false,
                    'property' => $lng->txt('size'),
                    'value' => ilFormat::formatSize(filesize($file), 'short'),
                    'propertyNameVisible' => false,
                    'newline' => true,
                );
                $props[] = array(
                    'alert' => false,
                    'newline' => true,
                    'property' => $this->getPlugin()->txt('request_upload_date'),
                    'value' => self::format_date_time($this->getXoctRequest()->getDateLastStatusChange()),
                    'propertyNameVisible' => true
                );

                if (!$this->getHasAccessToDownload()) {
                    $props[] = array(
                        'alert' => true,
                        'newline' => true,
                        'property' => 'description',
                        'value' => $this->getPlugin()->txt('status_no_access_to_download'),
                        'propertyNameVisible' => false
                    );
                }

                break;
        }

        return $props;
    }




    /**
     * @param $unix_timestamp
     *
     * @return string formatted date
     */

    protected function format_date_time($unix_timestamp) {
        $now = time();
        $today = $now - $now % (60 * 60 * 24);
        $yesterday = $today - 60 * 60 * 24;

        if ($unix_timestamp < $yesterday) {
            // given date is older than two days
            $date = date('d. M Y', $unix_timestamp);
        } elseif ($unix_timestamp < $today) {
            // given date yesterday
            $date = $this->lng->txt('yesterday');
        } else {
            // given date is today
            $date = $this->lng->txt('today');
        }

        return $date . ', ' . date('H:i', $unix_timestamp);
    }
}

?>
