<?php
/**
 * Class xoctIVTGroupParticipantGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 * @ilCtrl_IsCalledBy xoctIVTGroupParticipantGUI:ilObjOpenCastGUI
 */
class xoctIVTGroupParticipantGUI extends xoctGUI
{

	/**
	 * @param xoctOpenCast $xoctOpenCast
	 */
	public function __construct(xoctOpenCast $xoctOpenCast = null)
	{
		parent::__construct();
		if ($xoctOpenCast instanceof xoctOpenCast)
		{
			$this->xoctOpenCast = $xoctOpenCast;
		} else
		{
			$this->xoctOpenCast = new xoctOpenCast ();
		}
		$this->tabs->setTabActive(ilObjOpenCastGUI::TAB_GROUPS);
		xoctWaiterGUI::loadLib();
		$this->tpl->addJavaScript($this->pl->getStyleSheetLocation('default/group_participants.js'));
	}


	/**
	 * @param $data
	 */
	protected function outJson($data)
	{
		header('Content-type: application/json');
		echo json_encode($data);
		exit;
	}


	protected function index()
	{
	}


	protected function getAvailable()
	{
		$data = array();
		/**
		 * @var $xoctGroupParticipant xoctIVTGroupParticipant
		 */
		foreach (xoctIVTGroupParticipant::getAvailable($_GET['ref_id'], $_GET['group_id']) as $xoctGroupParticipant)
		{
			$stdClass = $xoctGroupParticipant->__asStdClass();
			$stdClass->name = $xoctGroupParticipant->getXoctUser()->getNamePresentation();
			$data[] = $stdClass;
		}

		usort($data, ['xoctGUI', 'compareStdClassByName']);

		$this->outJson($data);
	}


	protected function getPerGroup()
	{
		$data = array();
		$group_id = $_GET['group_id'];
		if (!$group_id)
		{
			$this->outJson(null);
		}
		/**
		 * @var $xoctGroupParticipant xoctIVTGroupParticipant
		 */
		foreach (xoctIVTGroupParticipant::where(array( 'group_id' => $group_id ))->get() as $xoctGroupParticipant)
		{
			$stdClass = $xoctGroupParticipant->__asStdClass();
			$stdClass->name = $xoctGroupParticipant->getXoctUser()->getNamePresentation();
			$data[] = $stdClass;
		}

		usort($data, ['xoctGUI', 'compareStdClassByName']);

		$this->outJson($data);
	}


	protected function add()
	{
		// TODO: Implement add() method.
	}


	protected function create()
	{
		if (!$_POST['user_id'] OR !$_POST['group_id'])
		{
			$this->outJson(false);
		}
		$xoctGroupParticipant = new xoctIVTGroupParticipant();
		$xoctGroupParticipant->setUserId($_POST['user_id']);
		$xoctGroupParticipant->setGroupId($_POST['group_id']);
		$xoctGroupParticipant->create();
		$this->outJson(true);
	}


	protected function edit()
	{
		// TODO: Implement edit() method.
	}


	protected function update()
	{
		// TODO: Implement update() method.
	}


	protected function confirmDelete()
	{
		// TODO: Implement confirmDelete() method.
	}


	protected function delete()
	{
		if (!$_POST['id'])
		{
			$this->outJson(false);
		}
		$o = new xoctIVTGroupParticipant($_POST['id']);
		$o->delete();
		$this->outJson(true);
	}
}