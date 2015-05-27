<?php
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/tests/openCastListGUI/class.proposedOpenCastListGUI.php');

require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/tests/class.mockedIlCtrl.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/tests/class.mockedIlPlugin.php');
require_once('./Customizing/global/plugins/Services/Repository/RepositoryObject/OpenCast/tests/class.mockedIlLanguage.php');

/**
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version $Id$
 */

class ilOpenCastListGUITest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ilOpenCastListGUI
     */
    protected $proposedOpenCastListGUI = null;

    /**
     * @var xoctRequest
     */
    protected $testRequest = null;

    public function ilOpenCastListGUITest()
    {
        date_default_timezone_set('UTC');
        $this->proposedOpenCastListGUI  = new proposedOpenCastListGUI();

        $mockedIlCtrl = new mockedIlCtrl();

        $mockedIlLanguage = new mockedIlLanguage();
        $mockedIlLanguage->setLanguageTestData(array(
            'status' => 'status',
            'yesterday' => 'yesterday',
            'today' => 'today'
        ));

        $mockedIlPlugin = new mockedIlPlugin();
        $mockedIlPlugin->setPluginTestData(array('txt'=>array(
            'common_cmd_delete' => 'delete',
            'request_creation_date' => 'request creation date',
            'request_status_STATUS_NEW' => 'new',
            'request_status_STATUS_IN_PROGRRESS' => 'in progress',
        )));

        $this->testRequest = new xoctRequest();
        $this->testRequest->setAuthor("testAuthor");
        $this->testRequest->setCreateDate("Today");
        $this->testRequest->getAbsoluteFilePath("/");
        $this->testRequest->setStatus(xoctRequest::STATUS_NEW);
        $this->testRequest->setTitle("testTitle");
        $this->testRequest->setPages(117);
        $this->testRequest->setCreateDate(1432038048);
        $this->testRequest->setPublishingYear(2014);
        $this->testRequest->setDateLastStatusChange(1432138048);

        $this->proposedOpenCastListGUI->init("xcst",$mockedIlPlugin, $mockedIlCtrl, $mockedIlLanguage,$this->testRequest,"showContent",false);
    }

    public function testType()
    {
        $this->assertEquals($this->proposedOpenCastListGUI->getType(), "xcst");
    }

    public function testGuiClass()
    {
        $this->assertEquals($this->proposedOpenCastListGUI->getGuiClass(), "ilObjOpenCastGUI");
    }

    public function testTitle()
    {
        $this->assertEquals($this->proposedOpenCastListGUI->getTitle(), "testTitle / testAuthor");
    }

    public function testDefaultCommand()
    {
        $this->assertEquals($this->proposedOpenCastListGUI->getDefaultCommand(), null);

        $this->testRequest->setStatus(xoctRequest::STATUS_COPY);
        $this->proposedOpenCastListGUI->setXoctRequest($this->testRequest);
        $this->proposedOpenCastListGUI->setHasAccessToDownload(true);
        $this->assertEquals($this->proposedOpenCastListGUI->getDefaultCommand(), array (
            'link' => 'MockedLinkTarget: class=ilObjOpenCastGUI cmd=showContent a_anchor= a_asynch= xml_style=1',
            'frame'  => '_top'
        ));
    }

    public function testCommands()
    {

        $this->assertEquals($this->proposedOpenCastListGUI->getCommands(), array (
            array('permission' => 'read','cmd' => 'showContent','default' => true),
            array('txt' => 'delete','permission' => 'delete','cmd' => 'confirmDeleteObject','default' => false)
        ));
        $this->assertFalse($this->proposedOpenCastListGUI->getTimingsEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getSubscribeEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getPaymentEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getLinkEnabled());
        $this->assertTrue($this->proposedOpenCastListGUI->getInfoScreenEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getDeleteEnabled());
        $this->assertTrue($this->proposedOpenCastListGUI->getCopyEnabled());
        $this->assertTrue($this->proposedOpenCastListGUI->getCutEnabled());


        $this->testRequest->setStatus(xoctRequest::STATUS_COPY);
        $this->proposedOpenCastListGUI->setXoctRequest($this->testRequest);
        $this->assertEquals($this->proposedOpenCastListGUI->getCommands(), array (
            array('permission' => 'read','cmd' => 'showContent','default' => true),
            array('txt' => 'delete','permission' => 'delete','cmd' => 'confirmDeleteObject','default' => false)
        ));
        $this->assertFalse($this->proposedOpenCastListGUI->getTimingsEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getSubscribeEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getPaymentEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getLinkEnabled());
        $this->assertTrue($this->proposedOpenCastListGUI->getInfoScreenEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getDeleteEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getCopyEnabled());
        $this->assertFalse($this->proposedOpenCastListGUI->getCutEnabled());

    }

    public function testProperties()
    {
        $this->assertEquals($this->proposedOpenCastListGUI->getProperties(), array(
            array('alert' => false, 'newline' => true,      'property' => 'description',            'value' => 'testTitle (2014), 117', 'propertyNameVisible' => false),
            array('alert' => true,  'newline' => true,      'property' => 'status',                 'value' => 'new',                   'propertyNameVisible' => true),
            array('alert' => false, 'newline' => true,      'property' => 'request creation date',  'value' => '19. May 2015, 12:20',   'propertyNameVisible' => true)
        ));

        $this->testRequest->setStatus(xoctRequest::STATUS_IN_PROGRRESS);
        $this->proposedOpenCastListGUI->setXoctRequest($this->testRequest);
        $this->assertEquals($this->proposedOpenCastListGUI->getProperties(), array(
            array('alert' => false, 'newline' => true,      'property' => 'description',            'value' => 'testTitle (2014), 117', 'propertyNameVisible' => false),
            array('alert' => true,  'newline' => true,      'property' => 'status',                 'value' => 'in progress',            'propertyNameVisible' => true),
            array('alert' => false, 'newline' => true,      'property' => 'request creation date',  'value' => '19. May 2015, 12:20',   'propertyNameVisible' => true)
        ));
    }
}
?>

