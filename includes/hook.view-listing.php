<?php
//##copyright##

if (!empty($item) && !empty($listing))
{
	$disabledItems = array('members');

	if (in_array($item, $disabledItems))
	{
		return;
	}

	$iaItem = $iaCore->factory('item');

	// check for ownership key
	if (isset($_GET['ownership-key']))
	{
		$iaDb->setTable('claim_pending_email_keys');

		$key = $iaDb->row_bind(iaDb::ALL_COLUMNS_SELECTION, '`item` = :item AND `item_id` = :id AND `key` = :key',
			array('item' => $item, 'id' => $listing, 'key' => $_GET['ownership-key']));

		if ($key)
		{
			$tableName = $iaItem->getItemTable($item);

			$iaDb->update(array('member_id' => $key['member_id']), iaDb::convertIds($listing), null, $tableName);
			$iaDb->delete(iaDb::convertIds($key['key'], 'key'));

			$iaView->setMessages(iaLanguage::get('ownership_changed'), iaView::SUCCESS);
			iaUtil::reload();
		}

		$iaDb->resetTable();
	}

	$itemTable = $iaItem->getItemTable($item);
	$itemData = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($listing), $itemTable);

	// check the current owner of the listing, if possible
	if (iaUsers::hasIdentity() && isset($itemData['member_id']) && iaUsers::getIdentity()->id == $itemData['member_id'])
	{
		return;
	}

	$actionsForGuest = array(
		'id' => 'claim-listing',
		'title' => iaLanguage::get('claim_listing'),
		'attributes' => array(
			'class' => 'btn btn-sm btn-default',
			'href' => IA_URL . 'claim/' . $item . '/' . $listing . '.json',
			'id' => 'js-cmd-claim',
			'data-toggle' => 'modal',
			'data-target' => '#js-claim-modal'
		)
	);

	$actionsForMember = array(
		'id' => 'claim-listing',
		'title' => iaLanguage::get('claim_listing'),
		'attributes' => array(
			'class' => 'btn btn-sm btn-default',
			'href' => '#',
			'onclick' => 'intelli.notifFloatBox({msg:\'' . iaSanitize::html(iaLanguage::get('sign_in_to_use_this_feature')) . '\',autohide:true}); return false;'
		)
	);

	$actionClaimListing = (iaUsers::hasIdentity())? $actionsForGuest : $actionsForMember;

	$iaView->assign('actionClaimListing', $actionClaimListing);
}