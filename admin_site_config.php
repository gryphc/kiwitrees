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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'admin_site_config.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';

$controller = new KT_Controller_Page();
$controller
	->restrictAccess(KT_USER_IS_ADMIN)
	->setPageTitle(KT_I18N::translate('Site configuration'))
	->addInlineJavascript('
		jQuery(function() {
			jQuery("div.site_config:odd").addClass("odd");
			jQuery("div.config_options:even").addClass("even");
		});
	');

// Lists of options for <select> controls.
$SMTP_SSL_OPTIONS = array(
	'none'=>KT_I18N::translate('none'),
	/* I18N: Secure Sockets Layer - a secure communications protocol*/ 'ssl'=>KT_I18N::translate('ssl'),
	/* I18N: Transport Layer Security - a secure communications protocol */ 'tls'=>KT_I18N::translate('tls'),
);
$SMTP_ACTIVE_OPTIONS = array(
	'internal'=>KT_I18N::translate('Use PHP mail to send messages'),
	'external'=>KT_I18N::translate('Use SMTP to send messages'),
);
$WELCOME_TEXT_AUTH_MODE_OPTIONS = array(
	0 => KT_I18N::translate('No predefined text'),
	1 => KT_I18N::translate('Predefined text that states all users can request a user account'),
	2 => KT_I18N::translate('Predefined text that states admin will decide on each request for a user account'),
	3 => KT_I18N::translate('Predefined text that states only family members can request a user account'),
	4 => KT_I18N::translate('Choose user defined welcome text typed below'),
);

switch (KT_Filter::post('action')) {
	case 'update':
		if (!KT_Filter::checkCsrf()) {
			break;
		}
		$INDEX_DIRECTORY = KT_Filter::post('INDEX_DIRECTORY');
		if (substr($INDEX_DIRECTORY, -1) !== '/') {
			$INDEX_DIRECTORY .= '/';
		}
		if (KT_File::mkdir($INDEX_DIRECTORY)) {
			KT_Site::preference('INDEX_DIRECTORY', $INDEX_DIRECTORY);
		} else {
			KT_FlashMessages::addMessage(I18N::translate('The folder %s does not exist, and it could not be created.', KT_Filter::escapeHtml($INDEX_DIRECTORY)));
		}
		KT_Site::preference('MEMORY_LIMIT',				KT_Filter::post('MEMORY_LIMIT'));
		KT_Site::preference('MAX_EXECUTION_TIME',		KT_Filter::post('MAX_EXECUTION_TIME'));
		KT_Site::preference('ALLOW_CHANGE_GEDCOM',		KT_Filter::postBool('ALLOW_CHANGE_GEDCOM'));
		KT_Site::preference('SESSION_TIME',				KT_Filter::post('SESSION_TIME'));
		KT_Site::preference('SERVER_URL',				KT_Filter::post('SERVER_URL'));
		KT_Site::preference('MAINTENANCE',				KT_Filter::postBool('MAINTENANCE'));
		KT_Site::preference('SMTP_ACTIVE',				KT_Filter::post('SMTP_ACTIVE'));
		KT_Site::preference('MAIL_FORMAT',				KT_Filter::postBool('MAIL_FORMAT'));
		KT_Site::preference('SMTP_FROM_NAME',			KT_Filter::post('SMTP_FROM_NAME'));
		KT_Site::preference('SMTP_HOST',				KT_Filter::post('SMTP_HOST'));
		KT_Site::preference('SMTP_PORT',				KT_Filter::post('SMTP_PORT'));
		KT_Site::preference('SMTP_AUTH',				KT_Filter::postBool('SMTP_AUTH'));
		KT_Site::preference('SMTP_AUTH_USER',			KT_Filter::post('SMTP_AUTH_USER'));
		KT_Site::preference('SMTP_SSL',					KT_Filter::post('SMTP_SSL'));
		KT_Site::preference('SMTP_HELO',				KT_Filter::post('SMTP_HELO'));
		if (KT_Filter::post('SMTP_AUTH_PASS')) {
			KT_Site::preference('SMTP_AUTH_PASS',		KT_Filter::post('SMTP_AUTH_PASS'));
		}
		KT_Site::preference('LOGIN_URL',				KT_Filter::post('LOGIN_URL'));
		KT_Site::preference('WELCOME_TEXT_AUTH_MODE',	KT_Filter::post('WELCOME_TEXT_AUTH_MODE'));
		KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE, KT_Filter::post('WELCOME_TEXT_AUTH_MODE_4'));
		KT_Site::preference('USE_REGISTRATION_MODULE',	KT_Filter::postBool('USE_REGISTRATION_MODULE'));
		KT_Site::preference('SHOW_REGISTER_CAUTION',	KT_Filter::postBool('SHOW_REGISTER_CAUTION'));
		KT_Site::preference('LANGUAGES', implode(',',	KT_Filter::postArray('LANGUAGES')));

		// Reload the page, so that the settings take effect immediately.
		Zend_Session::writeClose();
		header('Location: ' . KT_SERVER_NAME . KT_SCRIPT_PATH . KT_SCRIPT_NAME);
		exit;

}

$controller
	->pageHeader()
	->addInlineJavascript('
		// run test on initial page load
		 checkSize();
		 // run test on resize of the window
		 jQuery(window).resize(checkSize);
		//Function to the css rule
		function checkSize(){
			 if (jQuery("h3.accordion").css("display") == "block" ){
				jQuery("#accordion").accordion({event: "click", collapsible: true, heightStyle: "content"});
			 } else {
				jQuery("#tabs").tabs();
			}
		}
		jQuery(function() {
			jQuery("div.config_options:odd").addClass("odd");
			jQuery("div.config_options:even").addClass("even");
		});

		var selectVal = jQuery("#smtp_select option:selected").val();
		if (selectVal == "external"){
			jQuery("#smtp_options").css({"display":"block"});
		} else {
			jQuery("#smtp_options").css({"display":"none"});
		};

		jQuery("#smtp_select").click("option", function() {
			var clickedOption = jQuery(this).val();
			if (clickedOption == "external") {
				jQuery("#smtp_options").css({"display":"block"});
			} else {
				jQuery("#smtp_options").css({"display":"none"});
			};
		});
	');
?>

<div id="site_config">
	<h2><?php echo KT_I18N::translate('Site configuration'); ?></h2>
	<form method="post" id="configform" name="configform" action="<?php echo KT_SCRIPT_NAME; ?>">
		<?php echo KT_Filter::getCsrf(); ?>
		<input type="hidden" name="action" value="update">
		<div id="tabs">
			<ul>
				<li>
					<a href="#site"><span><?php echo KT_I18N::translate('Site configuration'); ?></span></a>
				</li>
				<li>
					<a href="#mail"><span><?php echo KT_I18N::translate('Mail configuration'); ?></span></a>
				</li>
				<li>
					<a href="#login"><span><?php echo KT_I18N::translate('Login & registration'); ?></span></a>
				</li>
				<li>
					<a href="#lang"><span><?php echo KT_I18N::translate('Languages'); ?></span></a>
				</li>
			</ul>
			<div id="accordion">
				<h3 class="accordion"><?php echo KT_I18N::translate('Site configuration'); ?></h3>
				<div id="site">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Data folder'); ?></label>
						<div class="input_group">
							<input type="text" name="INDEX_DIRECTORY" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('INDEX_DIRECTORY')); ?>" placeholder="data/" required>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the "Data folder" site configuration setting. “Apache” is a software program. */ KT_I18N::translate('This folder will be used by kiwitrees to store media files, GEDCOM files, temporary files, etc. The default setting is “data/”.<br>These files may contain private data and should not be made available over the internet. To protect this private data kiwitrees uses an Apache configuration file (.htaccess) which blocks all access to this folder.<br>If your web-server does not support .htaccess files and you cannot restrict access to this folder then you can select another folder away from your web documents. If you select a different folder you must also move all files (except config.ini.php, index.php and .htaccess) from the existing folder to the new folder. The folder can be specified here in full (e.g. /home/user_name/kiwitrees_data/) or relative to the installation folder (e.g. ../../kiwitrees_data/).'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Memory limit'); ?></label>
						<div class="input_group">
							<input type="text" name="MEMORY_LIMIT" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('MEMORY_LIMIT')); ?>" pattern="[0-9]+[KMG]" placeholder="<?php echo get_cfg_var('memory_limit'); ?>" maxlength="255">
							<div class="helpcontent">
								<?php echo /* I18N: %s is an amount of memory, such as 32MB */ KT_I18N::translate('By default, your server allows scripts to use %s of memory.', get_cfg_var('memory_limit')); ?>
								<p>
									<?php echo KT_I18N::translate('You can request a higher or lower limit here, although the server may ignore this request.<br>If you leave this setting empty the default value will be used.'); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('PHP time limit'); ?></label>
						<div class="input_group">
							<input type="text" name="MAX_EXECUTION_TIME" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('MAX_EXECUTION_TIME')); ?>" pattern="[0-9]*" placeholder="<?php echo get_cfg_var('max_execution_time') ?>" maxlength="255">
							<div class="helpcontent">
								<?php echo KT_I18N::plural(
									'By default, your server allows scripts to run for %s second.',
									'By default, your server allows scripts to run for %s seconds.',
									get_cfg_var('max_execution_time'), KT_I18N::number(get_cfg_var('max_execution_time'))
								); ?>
								<p>
									<?php echo KT_I18N::translate('You can request a higher or lower limit here, although the server may ignore this request.<br>If you leave this setting empty the default value will be used.'); ?>
								</p>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show list of family trees'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('ALLOW_CHANGE_GEDCOM', KT_Site::preference('ALLOW_CHANGE_GEDCOM')); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Show list of family trees” site configuration setting */ KT_I18N::translate('For sites with more than one family tree, this option will show the list of family trees in the main menu, the search pages, etc.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Session timeout'); ?></label>
						<div class="input_group">
							<input type="text" name="SESSION_TIME" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SESSION_TIME')); ?>" pattern="[0-9]*" placeholder="7200" maxlength="255">
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Session timeout” site configuration setting */ KT_I18N::translate('The time in seconds that a kiwitrees session remains active before requiring a login. The default is 7200, which is 2 hours.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Website URL'); ?></label>
						<div class="input_group">
							<?php echo select_edit_control('SERVER_URL', array(KT_SERVER_NAME.KT_SCRIPT_PATH=>KT_SERVER_NAME.KT_SCRIPT_PATH), '', KT_Site::preference('SERVER_URL')); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the "Website URL" site configuration setting */ KT_I18N::translate('If your site can be reached using more than one URL such as <b>http://www.example.com/kiwitrees/</b> and <b>http://kiwitrees.example.com/</b> you can specify the preferred URL here. Requests for the other URLs will be redirected to the preferred one. <span class="warning">If not required, leave this field blank.</span>'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Site maintenance'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('MAINTENANCE', KT_Site::preference('MAINTENANCE')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Set this to <b>yes</b> to temporarily prevent anyone <u>except the site administrator</u> from accessing your site.'); ?>
							</div>
						</div>
					</div>
				</div>
				<!-- MAIL TAB -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Mail configuration'); ?></h3>
				<div id="mail">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Messages'); ?></label>
						<div class="input_group">
							<select id="smtp_select" name="SMTP_ACTIVE">
								<?php foreach ($SMTP_ACTIVE_OPTIONS as $key=>$value) {
									echo '<option value="' . $key . '"';
									if (KT_Site::preference('SMTP_ACTIVE') == $key) echo ' selected="selected"';
									echo '>' . $value . '</option>';
								} ?>
							</select>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Messages” site configuration setting */ KT_I18N::translate('Kiwitrees needs to send emails such as password reminders and site notifications. To do this it can use this server\'s built in PHP mail facility (which is not always available) or an external SMTP (mail-relay) service, for which you will need to provide the connection details.<br>Selecting SMTP will display additional configuration options below.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Send mail in HTML format'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('MAIL_FORMAT', KT_Site::preference('MAIL_FORMAT')); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Messages” site configuration setting */ KT_I18N::translate('By default kiwitrees sends emails in plain text format. Setting this option to <b>yes</b> will change that to the multipart format. This allows the use of HTML formatting, but also includes a plain text version for recipients that do not allow HTML formatted emails.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Sender email'); ?></label>
						<div class="input_group">
							<input type="text" name="SMTP_FROM_NAME" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_FROM_NAME')); ?>" placeholder="no-reply@localhost" maxlength="255">
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Sender name” site configuration setting */ KT_I18N::translate('This name is used in the “From” field, when sending automatic emails from this server. It must be a valid email address.'); ?>
							</div>
						</div>
					</div>
					<!-- SMTP SECTION -->
					<div id="smtp_options" style="display:none;">
						<h4 class="accepted"><?php echo KT_I18N::translate('SMTP mail server settings'); ?></h4>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Server name'); ?></label>
							<div class="input_group">
								<input type="text" name="SMTP_HOST" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_HOST')); ?>" placeholder="smtp.example.com" pattern="[a-z0-9-]+(\.[a-z0-9-]+)*" maxlength="255">
								<div class="helpcontent">
									<?php echo /* I18N: Help text for the “Server name” site configuration setting */ KT_I18N::translate('This is the name of the SMTP server. \'localhost\' means that the mail service is running on the same computer as your web server.'); ?>
								</div>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Port number'); ?></label>
							<div class="input_group">
								<input type="text" name="SMTP_PORT" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_PORT')); ?>" pattern="[0-9]*" placeholder="25" maxlength="5">
								<div class="helpcontent">
									<?php echo /* I18N: Help text for the "Port number" site configuration setting */ KT_I18N::translate('By default SMTP works on port 25.'); ?>
								</div>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Use password'); ?></label>
							<div class="input_group">
								<?php echo edit_field_yes_no('SMTP_AUTH', KT_Site::preference('SMTP_AUTH')); ?>
								<div class="helpcontent">
									<?php echo /* I18N: Help text for the “Use password” site configuration setting */ KT_I18N::translate('Most SMTP servers require a password.'); ?>
								</div>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Username'); ?></label>
							<div class="input_group">
								<input type="text" name="SMTP_AUTH_USER" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_AUTH_USER')); ?>">
								<div class="helpcontent">
									<?php echo KT_I18N::translate('The user name required for authentication with the SMTP server.'); ?>
								</div>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Password'); ?></label>
							<div class="input_group">
								<input type="text" name="SMTP_AUTH_PASS" dir="ltr" value="">
								<div class="helpcontent">
									<?php echo KT_I18N::translate('The password required for authentication with the SMTP server.'); ?>
								</div>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Secure connection'); ?></label>
							<div class="input_group">
								<?php echo select_edit_control('SMTP_SSL', $SMTP_SSL_OPTIONS, null, KT_Site::preference('SMTP_SSL')); ?>
								<div class="helpcontent">
									<?php echo /* I18N: Help text for the "Secure connection" site configuration setting */ KT_I18N::translate('Most servers do not use secure connections.'); ?>
								</div>
							</div>
						</div>
						<div class="config_options">
							<label><?php echo KT_I18N::translate('Sending server name'); ?></label>
							<div class="input_group">
								<input type="text" name="SMTP_HELO" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('SMTP_HELO')); ?>" placeholder="localhost" pattern="[a-z0-9-]+(\.[a-z0-9-]+)*" maxlength="255">
								<div class="helpcontent">
									<?php echo /* I18N: Help text for the “Sending server name” site configuration setting */ KT_I18N::translate('Many mail servers require that the sending server identifies itself correctly, using a valid domain name.'); ?>
								</div>
							</div>
						</div>
						<p class="alert">
							<?php echo KT_I18N::translate('To use a Google mail account, use the following settings: server=smtp.gmail.com, port=587, security=tls, username=xxxxx@gmail.com, password=[your gmail password].<br>You must also enable “less secure applications” in your Google account. <a href="https://support.google.com/a/answer/6260879">https://support.google.com/a/answer/6260879</a>'); ?>
						</p>
					</div>
				</div>
				<!-- LOGIN TAB -->
				<h3 class="accordion"><?php echo KT_I18N::translate('Login & registration'); ?></h3>
				<div id="login">
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Login URL'); ?></label>
						<div class="input_group">
							<input type="text" name="LOGIN_URL" dir="ltr" value="<?php echo KT_Filter::escapeHtml(KT_Site::preference('LOGIN_URL')); ?>" maxlength="255">
							<div class="helpcontent">
								<?php echo /* I18N: Help text for the “Login URL” site configuration setting */ KT_I18N::translate('You only need to enter a Login URL if you want to redirect to a different site or location when your users login. This is very useful if you need to switch from http to https when your users login. Include the full URL to <i>login.php</i>. For example, https://www.yourserver.com/kiwitrees/login.php .'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Welcome text on login page'); ?></label>
						<div class="input_group">
							<?php echo select_edit_control('WELCOME_TEXT_AUTH_MODE', $WELCOME_TEXT_AUTH_MODE_OPTIONS, null, KT_Site::preference('WELCOME_TEXT_AUTH_MODE')); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Explanation for custom welcome text */ KT_I18N::translate('Here you can choose text to appear on the login page. You must determine which predefined text is most appropriate. You can also choose to enter your own custom welcome text.<br><br>Please refer to the Help text associated with the <b>Custom welcome text</b> field for more information.<br>The predefined texts are:<ul><li><b>Predefined text that states all users can request a user account:</b><div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to every visitor who has a user account.<br>If you have a user account, you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying your application, the site administrator will activate your account. You will receive an email when your application has been approved.</center></div><br/></li><li><b>Predefined text that states admin will decide on each request for a user account:</b><div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to <u>authorized</u> users only.<br>If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying your information, the administrator will either approve or decline your account application. You will receive an email message when your application has been approved.</center></div><br/></li><li><b>Predefined text that states only family members can request a user account:</b><div class="list_value_wrap"><center><b>Welcome to this Genealogy website</b><br>Access to this site is permitted to <u>family members only</u>.<br>If you have a user account you can login on this page. If you don\'t have a user account, you can apply for one by clicking on the appropriate link below.<br>After verifying the information you provide, the administrator will either approve or decline your request for an account. You will receive an email when your request is approved.</center></div></li></ul>'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Custom welcome text'); ?></label>
						<div class="input_group">
							<textarea maxlength="2000" id="WELCOME_TEXT_AUTH_MODE_4" name="WELCOME_TEXT_AUTH_MODE_4" rows="4"><?php echo KT_Filter::escapeHtml(KT_Site::preference('WELCOME_TEXT_AUTH_MODE_' . KT_LOCALE)) ?></textarea>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('If you have opted for custom welcome text, you can type that text here. To set this text for other languages you must switch to that language and visit this page again.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Allow visitors to request account registration'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('USE_REGISTRATION_MODULE', KT_Site::preference('USE_REGISTRATION_MODULE')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('Gives visitors the option of registering themselves for an account on the site. The visitor will receive an email message with a code to verify their application for an account. After verification the Administrator will have to approve the registration before it becomes active.'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo KT_I18N::translate('Show acceptable use agreement on «Request new user account» page'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('SHOW_REGISTER_CAUTION', KT_Site::preference('SHOW_REGISTER_CAUTION')); ?>
							<div class="helpcontent">
								<?php echo KT_I18N::translate('When set to <b>Yes</b>, the following message will appear above the input fields on the «Request new user account» page:<div class="list_value_wrap"><div class="largeError">Notice:</div><div class="error">By completing and submitting this form, you agree:<ul><li>to protect the privacy of living people listed on our site;</li><li>and in the text box below, to explain to whom you are related, or to provide us with information on someone who should be listed on our site.</li></ul></div></div>'); ?>
							</div>
						</div>
					</div>
				</div>
				<h3 class="accordion"><?php echo KT_I18N::translate('Languages'); ?></h3>
				<div id="lang">
					<h4><?php echo KT_I18N::translate('Select the languages your site will use'); ?></h4>
					<h5>
						<?php echo KT_I18N::translate('Select all'); ?>
						<input type="checkbox" onclick="toggle_select(this)" >
					</h5>
					<?php
						$code_list = KT_Site::preference('LANGUAGES');
						if ($code_list) {
							$languages = explode(',', $code_list);
						} else {
							$languages = array(
								'ar', 'bg', 'ca', 'cs', 'da', 'de', 'el', 'en_GB', 'en_US', 'es',
								'et', 'fi', 'fr', 'he', 'hr', 'hu', 'is', 'it', 'ka', 'lt', 'nb',
								'nl', 'nn', 'pl', 'pt', 'ru', 'sk', 'sv', 'tr', 'uk', 'vi', 'zh',
							);
						}
						foreach (KT_I18N::installed_languages() as $code=>$name) {
							echo '
								<span>
									<input class="check" type="checkbox" name="LANGUAGES[]" id="lang_' . $code . '"';
										if (in_array($code, $languages)) {
											echo 'checked="checked"';
										}
									echo ' value="' . $code . '">
									<label for="lang_' . $code . '"> '. KT_I18N::translate($name) . '</label>
								</span>
							';
						}
					?>
				</div>
			</div>
		</div>
		<p>
			<button type="submit" class="btn btn-primary">
				<i class="fa fa-floppy-o"></i>
				<?php echo KT_I18N::translate('save'); ?>
			</button>
		</p>
	</form>
</div>
