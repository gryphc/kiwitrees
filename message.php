<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'message.php');
require './includes/session.php';
require_once KT_ROOT . 'includes/functions/functions_mail.php';

$controller = new KT_Controller_Page();
$controller->setPageTitle(KT_I18N::translate('Contact us'));

if (array_key_exists('ckeditor', KT_Module::getActiveModules()) && KT_Site::preference('MAIL_FORMAT') == "1") {
	ckeditor_KT_Module::enableBasicEditor($controller);
}

// Send the message.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$to         = KT_Filter::post('to', null, '');
	$from_name  = KT_Filter::post('from_name', null, '');
	$from_email = KT_Filter::post('from_email');
	$subject    = KT_Filter::post('subject', null, '');
	$body       = KT_Filter::post('body', null, '');
	$url        = KT_Filter::postUrl('url', 'index.php');

	// Only an administration can use the distribution lists.
	$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);

	$recipients = recipients($to);

	// Different validation for admin/user/visitor.
	$errors = false;
	if (KT_USER_ID) {
		$from_name  = getUserFullName(KT_USER_ID);
		$from_email = getUserEmail(KT_USER_ID);
	} elseif ($from_name === '' || $from_email === '') {
		$errors = true;
	} elseif (!preg_match('/@(.+)/', $from_email, $match) || function_exists('checkdnsrr') && !checkdnsrr($match[1])) {
		KT_FlashMessages::addMessage(KT_I18N::translate('Please enter a valid email address.'), 'danger');
		$errors = true;
	} elseif (preg_match('/(?!' . preg_quote(KT_SERVER_NAME, '/') . ')(((?:ftp|http|https):\/\/)[a-zA-Z0-9.-]+)/', $subject . $body, $match)) {
		KT_FlashMessages::addMessage(KT_I18N::translate('You are not allowed to send messages that contain external links.') . ' ' . /* I18N: e.g. ‘You should delete the “http://” from “http://www.example.com” and try again.’ */ KT_I18N::translate('You should delete the “%1$s” from “%2$s” and try again.', $match[2], $match[1]), 'danger');
		$errors = true;
	} elseif (empty($recipients)) {
		$errors = true;
	}

	if ($errors) {
		// Errors? Go back to the form.
		header(
			'Location: message.php' .
			'?to=' . rawurlencode($to) .
			'&from_name=' . rawurlencode($from_name) .
			'&from_email=' . rawurlencode($from_name) .
			'&subject=' . rawurlencode($subject) .
			'&body=' . rawurlencode($body) .
			'&url=' . rawurlencode($url) .
			'&method=' . rawurlencode($method)
		);
	} else {
		// No errors.  Send the message.
		foreach ($recipients as $recipient) {
			$message         = array();
			$message['to']   = $to;
			$message['from'] = $from_email;
			if (!empty($from_name)) {
				$message['from_name']  = $from_name;
				$message['from_email'] = $from_email;
			}
			$message['subject'] = $subject;
			$message['body']    = nl2br($body, false);
			$message['url']     = $url;
			if (addMessage($message)) {
				KT_FlashMessages::addMessage(KT_I18N::translate('The message was successfully sent to %s.', KT_Filter::escapeHtml($to)));
			} else {
				KT_FlashMessages::addMessage(KT_I18N::translate('The message was not sent.'));
				AddToLog('Unable to send a message. FROM:' . $from_email . ' TO:' . getUserEmail($recipient), 'error');
			}
		}

		header('Location: ' . $url);

	}

	return;
}

$to         = KT_Filter::get('to', null, '');
$from_name  = KT_Filter::get('from_name', null, '');
$from_email = KT_Filter::get('from_email', '');
$subject    = KT_Filter::get('subject', null, '');
$body       = KT_Filter::get('body', null, '');
$url        = KT_Filter::getUrl('url', 'index.php');

// Only an administrator can use the distribution lists.
$controller->restrictAccess(!in_array($to, ['all', 'never_logged', 'last_6mo']) || KT_USER_IS_ADMIN);
$controller->pageHeader();

$to_names = implode(KT_I18N::$list_separator, array_map(function($user) { return getUserFullName($user); }, recipients($to))); ?>

<div id="contact_page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<?php echo messageForm($to, $from_name, $from_email, $subject, $body, $url, $to_names); ?>
</div>

<?php
