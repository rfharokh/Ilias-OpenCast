<?php
/**
 * Class xoctIVTGroupGUI
 *
 * @author            Fabian Schmid <fs@studer-raimann.ch>
 *
 * @ilCtrl_IsCalledBy xoctIVTGroupGUI: ilObjOpenCastGUI
 */
class xoctIVTGroupGUI extends xoctGUI {

	/**
	 * @param xoctOpenCast $xoctOpenCast
	 */
	public function __construct(xoctOpenCast $xoctOpenCast = NULL) {
		parent::__construct();
		if ($xoctOpenCast instanceof xoctOpenCast) {
			$this->xoctOpenCast = $xoctOpenCast;
		} else {
			$this->xoctOpenCast = new xoctOpenCast ();
		}
		$this->tabs->setTabActive(ilObjOpenCastGUI::TAB_GROUPS);
		//		xoctGroup::installDB();
		xoctWaiterGUI::loadLib();
		$this->tpl->addCss($this->pl->getStyleSheetLocation('default/groups.css'));
		$this->tpl->addJavaScript($this->pl->getStyleSheetLocation('default/groups.js'));
	}


	public function executeCommand() {
		global $DIC;
		$tree = $DIC['tree'];
		if (! ilObjOpenCastAccess::checkAction(ilObjOpenCastAccess::ACTION_MANAGE_IVT_GROUPS) ||
			!($tree->checkForParentType($_GET['ref_id'], 'crs') || $tree->checkForParentType($_GET['ref_id'], 'grp'))) {
			$this->ctrl->redirectByClass('xoctEventGUI');
		}
		parent::executeCommand();
	}


	protected function index() {
		$temp = $this->pl->getTemplate('default/tpl.groups.html', false, false);
		$temp->setVariable('HEADER_GROUPS', $this->pl->txt('groups_header'));
		$temp->setVariable('HEADER_PARTICIPANTS', $this->pl->txt('groups_participants_header'));
		$temp->setVariable('HEADER_PARTICIPANTS_AVAILABLE', $this->pl->txt('groups_available_participants_header'));
		$temp->setVariable('L_GROUP_NAME', $this->pl->txt('groups_new'));
		$temp->setVariable('PH_GROUP_NAME', $this->pl->txt('groups_new_placeholder'));
		$temp->setVariable('L_FILTER', $this->pl->txt('groups_participants_filter'));
		$temp->setVariable('PH_FILTER', $this->pl->txt('groups_participants_filter_placeholder'));
		$temp->setVariable('BUTTON_GROUP_NAME', $this->pl->txt('groups_new_button'));
		$temp->setVariable('BASE_URL', ($this->ctrl->getLinkTarget($this, '', '', true)));
		$temp->setVariable('GP_BASE_URL', ($this->ctrl->getLinkTarget(new xoctIVTGroupParticipantGUI($this->xoctOpenCast), '', '', true)));
		$temp->setVariable('GROUP_LANGUAGE', json_encode(array(
			'no_title' => $this->pl->txt('group_alert_no_title'),
			'delete_group' => $this->pl->txt('group_alert_delete_group'),
			'none_available' => $this->pl->txt('group_none_available')
		)));
		$temp->setVariable('PARTICIPANTS_LANGUAGE', json_encode(array(
			'delete_participant' => $this->pl->txt('group_delete_participant'),
			'select_group' => $this->pl->txt('group_select_group'),
			'none_available' => $this->pl->txt('group_none_available'),
			'none_available_all' => $this->pl->txt('group_none_available_all'),

		)));

		$this->tpl->setContent($temp->get());
	}


	/**
	 * @param $data
	 */
	protected function outJson($data) {
		header('Content-type: application/json');
		echo json_encode($data);
		exit;
	}


	protected function add() {
		// TODO: Implement add() method.
	}


	public function getAll() {
		$arr = array();
		foreach (xoctIVTGroup::getAllForId($this->xoctOpenCast->getObjId()) as $group) {
			$stdClass = $group->__asStdClass();
			$stdClass->user_count = xoctIVTGroupParticipant::where(array( 'group_id' => $group->getId() ))->count();
			$stdClass->name = $stdClass->title;
			$arr[] = $stdClass;
		}
		usort($arr, ['xoctGUI', 'compareStdClassByName']);
		$this->outJson($arr);
	}


	protected function create() {
		$obj = new xoctIVTGroup();
		$obj->setSerieId($this->xoctOpenCast->getObjId());
		$obj->setTitle($_POST['title']);
		$obj->create();
		$this->outJson($obj->__asStdClass());
	}


	protected function edit() {
		// TODO: Implement edit() method.
	}


	protected function update() {
		// TODO: Implement update() method.
	}


	protected function confirmDelete() {
		// TODO: Implement confirmDelete() method.
	}


	protected function delete() {
		/**
		 * @var $xoctIVTGroup xoctIVTGroup
		 */
		$status = false;
		$xoctIVTGroup = xoctIVTGroup::find($_GET['id']);
		if ($xoctIVTGroup->getSerieId() == $this->xoctOpenCast->getObjId()) {
			$xoctIVTGroup->delete();
			$status = true;
		}
		$this->outJson($status);
	}
}