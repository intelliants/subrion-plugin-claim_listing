<?php
//##copyright##

class iaBackendController extends iaAbstractControllerPluginBackend
{
	protected $_name = 'claims';

	protected $_pluginName = 'claim_listing';

	protected $_gridColumns = array('type', 'item', 'item_id', 'ip', 'status', 'date', 'view' => 1);
	//protected $_gridFilters = array('status' => 'equal');

	protected $_processAdd = true;


	protected function _indexPage(&$iaView)
	{
		if (isset($_POST['data-approve']))
		{
			$this->_approveClaim($_POST);
		}

		if (2 == count($this->_iaCore->requestPath) && 'view' == $this->_iaCore->requestPath[0])
		{
			$this->_viewPage($iaView, $this->_iaCore->requestPath[1]);
			return;
		}

		$iaView->grid('_IA_URL_plugins/' . $this->getPluginName() . '/js/admin/index');
	}

	protected function _modifyGridResult(array &$entries)
	{
		foreach ($entries as &$entry)
		{
			$entry['item'] = iaLanguage::get($entry['item']);
			$entry['type'] = iaLanguage::get('claim_approval_' . $entry['type']);
			$entry['ip'] = long2ip($entry['ip']);
		}
	}

	private function _viewPage(&$iaView, $id)
	{
		$item = $this->getById($id);

		if (!$item)
		{
			return iaView::errorPage(iaView::ERROR_NOT_FOUND);
		}

		$iaUsers = $this->_iaCore->factory('users');


		$iaView->assign('item', $item);
		$iaView->assign('initiator', $iaUsers->getInfo($item['member_id']));

		iaBreadcrumb::toEnd(iaLanguage::get('claim_details'));
		$iaView->title(iaLanguage::get('claim_details'));

		$iaView->display('view');
	}

	private function _approveClaim(array $data)
	{
		$claim = $this->getById($data['id']);

		$iaItem = $this->_iaCore->factory('item');


		$result = (bool)$this->_iaDb->update(
			array('member_id' => $claim['member_id']),
			iaDb::convertIds($claim['item_id']),
			null, $iaItem->getItemTable($claim['item']));

		$this->_iaDb->update(array('status' => 'approved'), iaDb::convertIds($claim['id']));

		if ($result && $this->_iaCore->get('claim_approved'))
		{
			$iaMailer = $this->_iaCore->factory('mailer');

			$iaMailer->loadTemplate('claim_approved');
			$iaMailer->addAddress($claim['email'], $claim['name']);
			$iaMailer->setReplacements(array(
				'listing_title' => $claim['item_title'],
				'listing_url' => $claim['item_url']
			));

			$iaMailer->send();
		}
	}
}