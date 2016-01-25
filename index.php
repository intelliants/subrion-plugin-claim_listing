<?php
//##copyright##

if (iaView::REQUEST_JSON == $iaView->getRequestType())
{
	if (empty($_POST) && 2 == count($iaCore->requestPath))
	{
		$iaItem = $iaCore->factory('item');
		$iaPage = $iaCore->factory('page', iaCore::FRONT);
		$iaUtil = $iaCore->factory('util');

		$item = iaSanitize::paranoid($iaCore->requestPath[0]);
		$itemId = iaSanitize::paranoid($iaCore->requestPath[1]);

		$itemTable = $iaItem->getItemTable($item);
		$itemData = $iaDb->row(iaDb::ALL_COLUMNS_SELECTION, iaDb::convertIds($itemId), $itemTable);

		$blockData = array(
			'formUrl' => $iaPage->getUrlByName('claim_listing') . 'process.json',
			'options' => array(
				'email' => $iaCore->get('cl_enable_email_approval') && isset($itemData['email']),
				'ftp' => $iaCore->get('cl_enable_ftp_approval') && !empty($itemData['url'])
			),
			'id' => $itemId,
			'item' => $item
		);

		if ($blockData['options']['email'])
		{
			$blockData['email'] = $itemData['email'];
		}

		if ($blockData['options']['ftp'])
		{
			// extra processing because of Subrion CMS URL fields saving specific
			$url = explode('|', $itemData['url']);
			$url = array_shift($url);
			$url = rtrim($url, IA_URL_DELIMITER) . IA_URL_DELIMITER;

			$blockData['url'] = $url;
			$blockData['filename'] = iaUtil::generateToken(30) . '.html';
		}


		$iaView->loadSmarty(true);
		$iaSmarty = &$iaView->iaSmarty;

		$iaSmarty->assign('claimListing', $blockData);

		echo $iaSmarty->fetch('plugins/claim_listing/templates/front/form.tpl');
		exit();
	}

	function httpCheckFile($url)
	{
		$curl = curl_init($url);

		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_setopt($curl, CURLOPT_HEADER, true);
		curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

		curl_exec($curl);

		$info = curl_getinfo($curl);

		curl_close($curl);

		if (200 !== $info['http_code'])
		{
			return array(false, iaLanguage::getf('http_status_error', array('code' => $info['http_code'])));
		}
		elseif (0 !== $info['redirect_count'])
		{
			return array(false, iaLanguage::get('extra_redirects_error'));
		}
		elseif (0 !== $info['download_content_length'] && -1 != $info['download_content_length'])
		{
			return array(false, iaLanguage::getf('remote_file_is_not_empty', array('bytes' => $info['download_content_length'])));
		}
		else
		{
			return array(true, iaLanguage::get('remote_file_validation_success'));
		}
	}

	if (isset($_POST['action']))
	{
		$output = array();

		switch ($_POST['action'])
		{
			case 'url':
				list($output['result'], $output['message']) = httpCheckFile($_POST['url'] . $_POST['filename']);
				if ($output['result'])
				{
					$iaDb->setTable('claim_pending_ftp_filenames');

					$stmt = iaDb::convertIds($_POST['url'], 'url');
					$iaDb->exists($stmt)
						? $iaDb->update(array('filename' => $_POST['filename']), $stmt)
						: $iaDb->insert(array('filename' => $_POST['filename'], 'url' => $_POST['url'], 'date' => date(iaDb::DATETIME_FORMAT)));

					$iaDb->resetTable();
				}
		}

		$iaView->assign($output);
		return;
	}


	$output = array('result' => false, 'message' => iaLanguage::get('unable_to_approve_listing'));

	if (!iaUsers::hasIdentity())
	{
		$iaView->assign($output);
		return;
	}

	$iaItem = $iaCore->factory('item');
	$iaCore->factory('util');

	$itemName = $_POST['item'];
	$itemId = $_POST['id'];

	if ($packageName = $iaItem->getPackageByItem($itemName))
	{
		$packageInstance = $iaCore->factoryPackage('item', $packageName, iaCore::FRONT, $itemName);
		$itemData = $packageInstance->getById($itemId);
		$itemUrl = $packageInstance->url('view', $itemData);

		$entry = array(
			'date' => date(iaDb::DATETIME_FORMAT),
			'ip' => iaUtil::getIp(),
			'member_id' => iaUsers::getIdentity()->id,
			'item' => $itemName,
			'item_id' => $itemId,
			'item_title' => $itemData['title'],
			'item_url' => $itemUrl,
			'type' => $_POST['type'],
			'status' => 'pending',
			'notes' => null
		);

		$message = null;

		switch ($_POST['type'])
		{
			case 'manual':
				$entry['name'] = empty($_POST['name']) ? null : $_POST['name'];
				$entry['email'] = empty($_POST['email']) ? null : $_POST['email'];
				$entry['phone'] = empty($_POST['phone']) ? null : $_POST['phone'];
				$entry['job_title'] = empty($_POST['job_title']) ? null : $_POST['job_title'];

				$message = iaLanguage::get('your_request_saved');

				break;

			case 'email':
				if (!$iaCore->get('cl_enable_email_approval'))
				{
					return;
				}

				if (!empty($itemData['email']))
				{
					$key = iaUtil::generateToken();

					$iaDb->setTable('claim_pending_email_keys');

					if ($rowId = $iaDb->one_bind(iaDb::ID_COLUMN_SELECTION, '`item` = :item AND `item_id` = :id',
						array('item' => $itemName, 'id' => $itemId)))
					{
						$iaDb->update(array('date' => date(iaDb::DATETIME_FORMAT), 'key' => $key, 'member_id' => iaUsers::getIdentity()->id),
							iaDb::convertIds($rowId));
					}
					else
					{
						$keyEntry = array(
							'date' => date(iaDb::DATETIME_FORMAT),
							'item' => $itemName,
							'item_id' => $itemId,
							'key' => $key,
							'member_id' => iaUsers::getIdentity()->id
						);

						$iaDb->insert($keyEntry);
					}

					$iaDb->resetTable();

					$link = $itemUrl . '?ownership-key=' . $key;


					$iaMailer = $iaCore->factory('mailer');

					$iaMailer->addAddress($itemData['email']);
					$iaMailer->loadTemplate('ownership_email_approval');
					$iaMailer->setReplacements(array('email' => $itemData['email'], 'url' => $link));

					$result = $iaMailer->send();

					$message = $result
						? iaLanguage::getf('confirmation_link_sent_with_email', array('email' => $itemData['email']))
						: iaLanguage::get('unable_to_send_confirmation_link');

					$entry['notes'] = $result
						? 'Approval link (' . $link . ') has been sent to ' . $itemData['email']
						: 'Email has not been sent to ' . $itemData['email'];
				}
				else
				{
					$entry['notes'] = 'Listing email was empty. No email sent.';
				}

				break;

			case 'ftp':
				if (!$iaCore->get('cl_enable_ftp_approval'))
				{
					return;
				}

				if (!empty($itemData['url']))
				{
					$url = explode('|', $itemData['url']);
					$url = array_shift($url);
					$url = rtrim($url, IA_URL_DELIMITER) . IA_URL_DELIMITER;

					$filenameRow = $iaDb->row(array('id', 'filename'), iaDb::convertIds($url, 'url'), 'claim_pending_ftp_filenames');

					if (empty($filenameRow))
					{
						return; // it seems someone is trying to hack us
					}

					$url = $url . $filenameRow['filename'];

					list($result, $error) = httpCheckFile($url);

					if ($result)
					{
						$tableName = $iaItem->getItemTable($itemName);

						$iaDb->update(array('member_id' => iaUsers::getIdentity()->id), iaDb::convertIds($itemId), null, $tableName);
						$iaDb->delete(iaDb::convertIds($filenameRow['id'], 'claim_pending_ftp_filenames'));

						$message = iaLanguage::get('ownership_changed');
						$entry['notes'] = 'Listing has been automatically assigned to the user ' . iaUsers::getIdentity()->fullname
							. '. Successfully validated the URL ' . $url;
					}
					else
					{
						$entry['notes'] = 'URL ' . $url . ' has not been validated: ' . $error;
					}
				}
		}

		$output['result'] = (bool)$iaDb->insert($entry, null, 'claims');
		$output['message'] = $output['result'] ? $message : iaLanguage::get('db_error');
	}

	$iaView->assign($output);
}

if (iaView::REQUEST_HTML == $iaView->getRequestType())
{
	return iaView::errorPage(iaView::ERROR_NOT_FOUND);
}