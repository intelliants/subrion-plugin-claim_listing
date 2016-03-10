<?php
/******************************************************************************
 *
 * Subrion - open source content management system
 * Copyright (C) 2016 Intelliants, LLC <http://www.intelliants.com>
 *
 * This file is part of Subrion.
 *
 * Subrion is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Subrion is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Subrion. If not, see <http://www.gnu.org/licenses/>.
 *
 *
 * @link http://www.subrion.org/
 *
 ******************************************************************************/

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