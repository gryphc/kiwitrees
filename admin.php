<?php
// Welcome page for the administration module
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

define('WT_SCRIPT_NAME', 'admin.php');

require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller=new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->addInlineJavascript('jQuery("#x").accordion({heightStyle: "content"});')
	->addInlineJavascript('jQuery("#tree_stats").accordion({event: "click"});') // add " hoverintent" to change from just click to hover
	->addInlineJavascript('jQuery("#changes").accordion({event: "click"});')
	->addInlineJavascript('jQuery("#content_container").css("visibility", "visible");')
	->setPageTitle(WT_I18N::translate('Administration'))
	->pageHeader();

$stats=new WT_Stats(WT_GEDCOM);
	$totusers  =0;       // Total number of users
	$warnusers =0;       // Users with warning
	$applusers =0;       // Users who have not verified themselves
	$nverusers =0;       // Users not verified by admin but verified themselves
	$adminusers=0;       // Administrators
	$userlang  =array(); // Array for user languages
	$gedadmin  =array(); // Array for managers

// Display a series of "blocks" of general information, vary according to admin or manager.

echo '<div id="content_container" style="visibility:hidden">';

echo '<div id="x">';// div x - manages the accordion effect

echo '<h2>', WT_WEBTREES, ' ', WT_VERSION, '</h2>',
	'<div id="about">',
		'<p>', WT_I18N::translate('These pages provide access to all the configuration settings and management tools for this <b>kiwitrees</b> site.'), '</p>',
		'<p>',  /* I18N: %s is a URL/link to the project website */ WT_I18N::translate('Support is available at %s.', ' <a class="current" href="http://kiwitrees.net/forums/">kiwitrees.net</a>'), '</p>',
	'</div>';


// Accordion block for DELETE OLD FILES - only shown when old files are found
$old_files_found=false;
foreach (old_paths() as $path) {
	if (file_exists($path)) {
		delete_recursively($path);
		// we may not have permission to delete.  Is it still there?
		if (file_exists($path)) {
			$old_files_found=true;
		}
	}
}

if (WT_USER_IS_ADMIN && $old_files_found) {
	echo
		'<h2><span class="warning">', WT_I18N::translate('Old files found'), '</span></h2>',
		'<div>',
		'<p>', WT_I18N::translate('Files have been found from a previous version of webtrees.  Old files can sometimes be a security risk.  You should delete them.'), '</p>',
		'<ul>';
		foreach (old_paths() as $path) {
			if (file_exists($path)) {
				echo '<li>', $path, '</li>';
			}
		}
	echo
		'</ul>',
		'</div>';
}
echo '</div>'; //id = content_container


foreach(get_all_users() as $user_id=>$user_name) {
	$totusers = $totusers + 1;
	if (((date("U") - (int)get_user_setting($user_id, 'reg_timestamp')) > 604800) && !get_user_setting($user_id, 'verified')) {
		$warnusers++;
	}
	if (!get_user_setting($user_id, 'verified_by_admin') && get_user_setting($user_id, 'verified')) {
		$nverusers++;
	}
	if (!get_user_setting($user_id, 'verified')) {
		$applusers++;
	}
	if (get_user_setting($user_id, 'canadmin')) {
		$adminusers++;
	}
	foreach (WT_Tree::getAll() as $tree) {
		if ($tree->userPreference($user_id, 'canedit')=='admin') {
			if (isset($gedadmin[$tree->tree_id])) {
				$gedadmin[$tree->tree_id]["number"]++;
			} else {
				$gedadmin[$tree->tree_id]["number"] = 1;
				$gedadmin[$tree->tree_id]["ged"] = $tree->tree_name;
				$gedadmin[$tree->tree_id]["title"] = $tree->tree_title_html;
			}
		}
	}
	if ($user_lang=get_user_setting($user_id, 'language')) {
		if (isset($userlang[$user_lang]))
			$userlang[$user_lang]["number"]++;
		else {
			$userlang[$user_lang]["langname"] = Zend_Locale::getTranslation($user_lang, 'language', WT_LOCALE);
			$userlang[$user_lang]["number"] = 1;
		}
	}
}	

echo '
<fieldset id="users">
	<legend>', WT_I18N::translate('Users'), '</legend>
	<ul class="admin_stats">
		<li>
			<span>', WT_I18N::translate('Total number of users'), '</span>
			<span class="filler">&nbsp;</span>
			<span>', $totusers, '</span>
		</li>
		<li>
			<span class="inset"><a href="admin_users.php?action=listusers&amp;filter=1">', WT_I18N::translate('Administrators'), '</a></span>
			<span class="filler">&nbsp;</span>
			<span>', $adminusers, '</span>
		</li>
		<li>
			<span class="inset">', WT_I18N::translate('Managers'), '</span>
			<span class="filler">&nbsp;</span>
			<span>&nbsp;</span>
		</li>';
		foreach ($gedadmin as $ged_id=>$geds) {
			echo '
			<li>
				<span class="inset2"><a href="admin_users.php?action=listusers&amp;filter=gedadmin&amp;ged='.rawurlencode($geds['ged']), '" dir="auto">', $geds['title'], '</a></span>
				<span class="filler">&nbsp;</span>
				<span>', $geds['number'], '</span>
			</li>';
		}
		echo '
		<li>
			<span>';
				if ($applusers == 0) {
					echo WT_I18N::translate('Unverified by User');
				} else {
					echo '<a href="admin_users.php?action=listusers&amp;filter=usunver">', WT_I18N::translate('Unverified by User'), '</a>';
				}
			echo '</span>
			<span class="filler">&nbsp;</span>
			<span>', $applusers, '</span>
		</li>
		<li>
			<span>';
				if ($nverusers == 0) {
					echo WT_I18N::translate('Unverified by Administrator');
				} else {
					echo '<a href="admin_users.php?action=listusers&amp;filter=admunver">', WT_I18N::translate('Unverified by Administrator'), '</a>';
				}
			echo '</span>
			<span class="filler">&nbsp;</span>
			<span>', $nverusers, '</span>
		</li>
		<li>
			<span>', WT_I18N::translate('Users\' languages'), '</span>
			<span class="filler">&nbsp;</span>
			<span>&nbsp;</span>
		</li>';
		foreach ($userlang as $key=>$ulang) {
			echo '
			<li>
				<span>&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin_users.php?action=listusers&amp;filter=language&amp;usrlang=', $key, '">', $ulang['langname'], '</a></span>
				<span class="filler">&nbsp;</span>
				<span>', $ulang['number'], '</span>
			</li>';
		}
	echo '</ul>
	<div id="logged_in_users">',
		WT_I18N::translate('Users Logged In'), '
		<div class="inset">',
			$stats->_usersLoggedIn('list'), '
		</div>
	</div>
</fieldset>'; // id = users

function siteIndividuals() {
	$count = WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##individuals`")
		->execute()
		->fetchOne();
	return	WT_I18N::number($count);
}

function siteMedia() {
	$count = WT_DB::prepare("SELECT SQL_CACHE COUNT(*) FROM `##media`")
		->execute()
		->fetchOne();
	return	WT_I18N::number($count);
}

$n = 0;

echo
'<fieldset id="trees">
	<legend>', WT_I18N::translate('Family tree statistics'), '</legend>',
	'<div id="tree_stats">';

		foreach (WT_Tree::getAll() as $tree) {
			$stats = new WT_Stats($tree->tree_name);
			if ($tree->tree_id==WT_GED_ID) {
				$accordion_element=$n;
			}
			++$n;
			echo '
			<h3>', $stats->gedcomTitle(), '</h3>
			<ul class="admin_stats">
				<li>
					<span><a href="indilist.php?ged=', $tree->tree_name_url, '">', WT_I18N::translate('Individuals'), '</a></span>
					<span class="filler">&nbsp;</span>
					<span>', $stats->totalIndividuals(),'</span>
				</li>
				<li>
					<span><a href="famlist.php?ged=', $tree->tree_name_url, '">', WT_I18N::translate('Families'), '</a></span>
					<span class="filler">&nbsp;</span>
					<span>', $stats->totalFamilies(), '</span>
				</li>
				<li>
					<span><a href="sourcelist.php?ged=', $tree->tree_name_url, '">', WT_I18N::translate('Sources'), '</a></span>
					<span class="filler">&nbsp;</span>
					<span>', $stats->totalSources(), '</span>
				</li>
				<li>
					<span><a href="repolist.php?ged=', $tree->tree_name_url, '">', WT_I18N::translate('Repositories'), '</a></span>
					<span class="filler">&nbsp;</span>
					<span>', $stats->totalRepositories(), '</span>
				</li>
				<li>
					<span><a href="medialist.php?ged=', $tree->tree_name_url, '">', WT_I18N::translate('Media objects'), '</a></span>
					<span class="filler">&nbsp;</span>
					<span>', $stats->totalMedia(), '</span>
				</li>
				<li>
					<span><a href="notelist.php?ged=', $tree->tree_name_url, '">', WT_I18N::translate('Notes'), '</a></span>
					<span class="filler">&nbsp;</span>
					<span>', $stats->totalNotes(), '</span>
				</li>
			</ul>';
		}
		echo '
		<h3>', WT_I18N::translate('All trees'), '</h3>
		<ul class="admin_stats">
			<li>
				<span>', WT_I18N::translate('Individuals'), '</span>
				<span class="filler">&nbsp;</span>
				<span>', siteIndividuals(),'</span>
			</li>
			<li>
				<span>', WT_I18N::translate('Media objects'), '</span>
				<span class="filler">&nbsp;</span>
				<span>', siteMedia(),'</span>
			</li>
			<li>
				<span>Your database size is currently</span>
				<span class="filler"></span>
				<span>', WT_I18N::number(db_size()), ' MB</span>
			</li>
			<li>
				<span>Your files are currently using</span>
				<span class="filler"></span>
				<span>', WT_I18N::number(directory_size()), ' MB</span>
			</li>
			<li>
				<span>Total server space used is therefore</span>
				<span class="filler"></span>
				<span>', WT_I18N::number(db_Size() + directory_size()), ' MB</span>
			</li>
		</ul>
	</div>', // id=tree_stats
'</fieldset>'; // id=trees

echo
	'<fieldset id="recent">
		<legend>', WT_I18N::translate('Recent changes'), '</legend>
		<div id="changes">';
$n=0;
foreach (WT_Tree::GetAll() as $tree) {
	if ($tree->tree_id==WT_GED_ID) {
		$accordion_element=$n;
	}
	++$n;
	echo 
		'<h3><span dir="auto">', $tree->tree_title_html, '</span></h3>',
		'<div>',
		'<table>',
		'<tr><th>&nbsp;</th><th><span>', WT_I18N::translate('Day'), '</span></th><th><span>', WT_I18N::translate('Week'), '</span></th><th><span>', WT_I18N::translate('Month'), '</span></th></tr>',
		'<tr><th>', WT_I18N::translate('Individuals'), '</th><td>', WT_Query_Admin::countIndiChangesToday($tree->tree_id), '</td><td>', WT_Query_Admin::countIndiChangesWeek($tree->tree_id), '</td><td>', WT_Query_Admin::countIndiChangesMonth($tree->tree_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Families'), '</th><td>', WT_Query_Admin::countFamChangesToday($tree->tree_id), '</td><td>', WT_Query_Admin::countFamChangesWeek($tree->tree_id), '</td><td>', WT_Query_Admin::countFamChangesMonth($tree->tree_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Sources'), '</th><td>',  WT_Query_Admin::countSourChangesToday($tree->tree_id), '</td><td>', WT_Query_Admin::countSourChangesWeek($tree->tree_id), '</td><td>', WT_Query_Admin::countSourChangesMonth($tree->tree_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Repositories'), '</th><td>',  WT_Query_Admin::countRepoChangesToday($tree->tree_id), '</td><td>', WT_Query_Admin::countRepoChangesWeek($tree->tree_id), '</td><td>', WT_Query_Admin::countRepoChangesMonth($tree->tree_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Media objects'), '</th><td>', WT_Query_Admin::countObjeChangesToday($tree->tree_id), '</td><td>', WT_Query_Admin::countObjeChangesWeek($tree->tree_id), '</td><td>', WT_Query_Admin::countObjeChangesMonth($tree->tree_id), '</td></tr>',
		'<tr><th>', WT_I18N::translate('Notes'), '</th><td>', WT_Query_Admin::countNoteChangesToday($tree->tree_id), '</td><td>', WT_Query_Admin::countNoteChangesWeek($tree->tree_id), '</td><td>', WT_Query_Admin::countNoteChangesMonth($tree->tree_id), '</td></tr>',
		'</table>',
		'</div>';
	}
echo
	'</div>', // id=changes
	'</div>'; //id = "x"

// This is a list of old files and directories, from earlier versions of webtrees, that can be deleted
// It was generated with the help of a command like this
// svn diff svn://svn.webtrees.net/tags/1.2.3 svn://svn.webtrees.net/trunk --summarize | grep ^D | sort
function old_paths() {
	return array(
		// Removed in 1.0.2
		WT_ROOT.'language/en.mo',
		// Removed in 1.0.3
		WT_ROOT.'themechange.php',
		// Removed in 1.0.4
		WT_ROOT.'themes/fab/images/notes.gif',
		// Removed in 1.0.5
		// Removed in 1.0.6
		WT_ROOT.'includes/extras',
		// Removed in 1.1.0
		WT_ROOT.'addremotelink.php',
		WT_ROOT.'addsearchlink.php',
		WT_ROOT.'client.php',
		WT_ROOT.'dir_editor.php',
		WT_ROOT.'editconfig_gedcom.php',
		WT_ROOT.'editgedcoms.php',
		WT_ROOT.'edit_merge.php',
		WT_ROOT.'genservice.php',
		WT_ROOT.'includes/classes',
		WT_ROOT.'includes/controllers',
		WT_ROOT.'includes/family_nav.php',
		WT_ROOT.'includes/functions/functions_lang.php',
		WT_ROOT.'includes/functions/functions_tools.php',
		WT_ROOT.'js/conio',
		WT_ROOT.'logs.php',
		WT_ROOT.'manageservers.php',
		WT_ROOT.'media.php',
		WT_ROOT.'module_admin.php',
		//WT_ROOT.'modules', // Do not delete - users may have stored custom modules/data here
		WT_ROOT.'opensearch.php',
		WT_ROOT.'PEAR.php',
		WT_ROOT.'pgv_to_wt.php',
		WT_ROOT.'places',
		//WT_ROOT.'robots.txt', // Do not delete this - it may contain user data
		WT_ROOT.'serviceClientTest.php',
		WT_ROOT.'siteconfig.php',
		WT_ROOT.'SOAP',
		WT_ROOT.'themes/clouds/images/xml.gif',
		WT_ROOT.'themes/clouds/mozilla.css',
		WT_ROOT.'themes/clouds/netscape.css',
		WT_ROOT.'themes/colors/images/xml.gif',
		WT_ROOT.'themes/colors/mozilla.css',
		WT_ROOT.'themes/colors/netscape.css',
		WT_ROOT.'themes/fab/images/checked.gif',
		WT_ROOT.'themes/fab/images/checked_qm.gif',
		WT_ROOT.'themes/fab/images/feed-icon16x16.png',
		WT_ROOT.'themes/fab/images/hcal.png',
		WT_ROOT.'themes/fab/images/menu_punbb.gif',
		WT_ROOT.'themes/fab/images/trashcan.gif',
		WT_ROOT.'themes/fab/images/xml.gif',
		WT_ROOT.'themes/fab/mozilla.css',
		WT_ROOT.'themes/fab/netscape.css',
		WT_ROOT.'themes/minimal/mozilla.css',
		WT_ROOT.'themes/minimal/netscape.css',
		WT_ROOT.'themes/webtrees/images/checked.gif',
		WT_ROOT.'themes/webtrees/images/checked_qm.gif',
		WT_ROOT.'themes/webtrees/images/feed-icon16x16.png',
		WT_ROOT.'themes/webtrees/images/header.jpg',
		WT_ROOT.'themes/webtrees/images/trashcan.gif',
		WT_ROOT.'themes/webtrees/images/xml.gif',
		WT_ROOT.'themes/webtrees/mozilla.css',
		WT_ROOT.'themes/webtrees/netscape.css',
		WT_ROOT.'themes/webtrees/style_rtl.css',
		WT_ROOT.'themes/xenea/mozilla.css',
		WT_ROOT.'themes/xenea/netscape.css',
		WT_ROOT.'uploadmedia.php',
		WT_ROOT.'useradmin.php',
		WT_ROOT.'webservice',
		WT_ROOT.'wtinfo.php',
		// Removed in 1.1.1
		WT_ROOT.'themes/webtrees/images/add.gif',
		WT_ROOT.'themes/webtrees/images/bubble.gif',
		WT_ROOT.'themes/webtrees/images/buttons/addmedia.gif',
		WT_ROOT.'themes/webtrees/images/buttons/addnote.gif',
		WT_ROOT.'themes/webtrees/images/buttons/addrepository.gif',
		WT_ROOT.'themes/webtrees/images/buttons/addsource.gif',
		WT_ROOT.'themes/webtrees/images/buttons/autocomplete.gif',
		WT_ROOT.'themes/webtrees/images/buttons/calendar.gif',
		WT_ROOT.'themes/webtrees/images/buttons/family.gif',
		WT_ROOT.'themes/webtrees/images/buttons/head.gif',
		WT_ROOT.'themes/webtrees/images/buttons/indi.gif',
		WT_ROOT.'themes/webtrees/images/buttons/keyboard.gif',
		WT_ROOT.'themes/webtrees/images/buttons/media.gif',
		WT_ROOT.'themes/webtrees/images/buttons/note.gif',
		WT_ROOT.'themes/webtrees/images/buttons/place.gif',
		WT_ROOT.'themes/webtrees/images/buttons/refresh.gif',
		WT_ROOT.'themes/webtrees/images/buttons/repository.gif',
		WT_ROOT.'themes/webtrees/images/buttons/source.gif',
		WT_ROOT.'themes/webtrees/images/buttons/target.gif',
		WT_ROOT.'themes/webtrees/images/buttons/view_all.gif',
		WT_ROOT.'themes/webtrees/images/cfamily.png',
		WT_ROOT.'themes/webtrees/images/childless.gif',
		WT_ROOT.'themes/webtrees/images/children.gif',
		WT_ROOT.'themes/webtrees/images/darrow2.gif',
		WT_ROOT.'themes/webtrees/images/darrow.gif',
		WT_ROOT.'themes/webtrees/images/ddarrow.gif',
		WT_ROOT.'themes/webtrees/images/dline2.gif',
		WT_ROOT.'themes/webtrees/images/dline.gif',
		WT_ROOT.'themes/webtrees/images/edit_sm.png',
		WT_ROOT.'themes/webtrees/images/fambook.png',
		WT_ROOT.'themes/webtrees/images/forbidden.gif',
		WT_ROOT.'themes/webtrees/images/hline.gif',
		WT_ROOT.'themes/webtrees/images/larrow2.gif',
		WT_ROOT.'themes/webtrees/images/larrow.gif',
		WT_ROOT.'themes/webtrees/images/ldarrow.gif',
		WT_ROOT.'themes/webtrees/images/lsdnarrow.gif',
		WT_ROOT.'themes/webtrees/images/lsltarrow.gif',
		WT_ROOT.'themes/webtrees/images/lsrtarrow.gif',
		WT_ROOT.'themes/webtrees/images/lsuparrow.gif',
		WT_ROOT.'themes/webtrees/images/mapq.gif',
		WT_ROOT.'themes/webtrees/images/media/doc.gif',
		WT_ROOT.'themes/webtrees/images/media/ged.gif',
		WT_ROOT.'themes/webtrees/images/media/globe.png',
		WT_ROOT.'themes/webtrees/images/media/html.gif',
		WT_ROOT.'themes/webtrees/images/media/pdf.gif',
		WT_ROOT.'themes/webtrees/images/media/tex.gif',
		WT_ROOT.'themes/webtrees/images/minus.gif',
		WT_ROOT.'themes/webtrees/images/move.gif',
		WT_ROOT.'themes/webtrees/images/multim.gif',
		WT_ROOT.'themes/webtrees/images/pix1.gif',
		WT_ROOT.'themes/webtrees/images/plus.gif',
		WT_ROOT.'themes/webtrees/images/rarrow2.gif',
		WT_ROOT.'themes/webtrees/images/rarrow.gif',
		WT_ROOT.'themes/webtrees/images/rdarrow.gif',
		WT_ROOT.'themes/webtrees/images/reminder.gif',
		WT_ROOT.'themes/webtrees/images/remove-dis.png',
		WT_ROOT.'themes/webtrees/images/remove.gif',
		WT_ROOT.'themes/webtrees/images/RESN_confidential.gif',
		WT_ROOT.'themes/webtrees/images/RESN_locked.gif',
		WT_ROOT.'themes/webtrees/images/RESN_none.gif',
		WT_ROOT.'themes/webtrees/images/RESN_privacy.gif',
		WT_ROOT.'themes/webtrees/images/rings.gif',
		WT_ROOT.'themes/webtrees/images/sex_f_15x15.gif',
		WT_ROOT.'themes/webtrees/images/sex_f_9x9.gif',
		WT_ROOT.'themes/webtrees/images/sex_m_15x15.gif',
		WT_ROOT.'themes/webtrees/images/sex_m_9x9.gif',
		WT_ROOT.'themes/webtrees/images/sex_u_15x15.gif',
		WT_ROOT.'themes/webtrees/images/sex_u_9x9.gif',
		WT_ROOT.'themes/webtrees/images/sfamily.png',
		WT_ROOT.'themes/webtrees/images/silhouette_female.gif',
		WT_ROOT.'themes/webtrees/images/silhouette_male.gif',
		WT_ROOT.'themes/webtrees/images/silhouette_unknown.gif',
		WT_ROOT.'themes/webtrees/images/spacer.gif',
		WT_ROOT.'themes/webtrees/images/stop.gif',
		WT_ROOT.'themes/webtrees/images/terrasrv.gif',
		WT_ROOT.'themes/webtrees/images/timelineChunk.gif',
		WT_ROOT.'themes/webtrees/images/topdown.gif',
		WT_ROOT.'themes/webtrees/images/uarrow2.gif',
		WT_ROOT.'themes/webtrees/images/uarrow3.gif',
		WT_ROOT.'themes/webtrees/images/uarrow.gif',
		WT_ROOT.'themes/webtrees/images/udarrow.gif',
		WT_ROOT.'themes/webtrees/images/video.png',
		WT_ROOT.'themes/webtrees/images/vline.gif',
		WT_ROOT.'themes/webtrees/images/warning.gif',
		WT_ROOT.'themes/webtrees/images/zoomin.gif',
		WT_ROOT.'themes/webtrees/images/zoomout.gif',
		// Removed in 1.1.2
		WT_ROOT.'js/treenav.js',
		WT_ROOT.'library/WT/TreeNav.php',
		WT_ROOT.'themes/clouds/images/background.jpg',
		WT_ROOT.'themes/clouds/images/buttons/refresh.gif',
		WT_ROOT.'themes/clouds/images/buttons/view_all.gif',
		WT_ROOT.'themes/clouds/images/lsdnarrow.gif',
		WT_ROOT.'themes/clouds/images/lsltarrow.gif',
		WT_ROOT.'themes/clouds/images/lsrtarrow.gif',
		WT_ROOT.'themes/clouds/images/lsuparrow.gif',
		WT_ROOT.'themes/clouds/images/menu_gallery.gif',
		WT_ROOT.'themes/clouds/images/menu_punbb.gif',
		WT_ROOT.'themes/clouds/images/menu_research.gif',
		WT_ROOT.'themes/clouds/images/silhouette_female.gif',
		WT_ROOT.'themes/clouds/images/silhouette_male.gif',
		WT_ROOT.'themes/clouds/images/silhouette_unknown.gif',
		WT_ROOT.'themes/colors/images/buttons/refresh.gif',
		WT_ROOT.'themes/colors/images/buttons/view_all.gif',
		WT_ROOT.'themes/colors/images/lsdnarrow.gif',
		WT_ROOT.'themes/colors/images/lsltarrow.gif',
		WT_ROOT.'themes/colors/images/lsrtarrow.gif',
		WT_ROOT.'themes/colors/images/lsuparrow.gif',
		WT_ROOT.'themes/colors/images/menu_gallery.gif',
		WT_ROOT.'themes/colors/images/menu_punbb.gif',
		WT_ROOT.'themes/colors/images/menu_research.gif',
		WT_ROOT.'themes/colors/images/silhouette_female.gif',
		WT_ROOT.'themes/colors/images/silhouette_male.gif',
		WT_ROOT.'themes/colors/images/silhouette_unknown.gif',
		WT_ROOT.'themes/fab/images/bubble.gif',
		WT_ROOT.'themes/fab/images/buttons/refresh.gif',
		WT_ROOT.'themes/fab/images/buttons/view_all.gif',
		WT_ROOT.'themes/fab/images/lsdnarrow.gif',
		WT_ROOT.'themes/fab/images/lsltarrow.gif',
		WT_ROOT.'themes/fab/images/lsrtarrow.gif',
		WT_ROOT.'themes/fab/images/lsuparrow.gif',
		WT_ROOT.'themes/fab/images/mapq.gif',
		WT_ROOT.'themes/fab/images/menu_gallery.gif',
		WT_ROOT.'themes/fab/images/menu_research.gif',
		WT_ROOT.'themes/fab/images/multim.gif',
		WT_ROOT.'themes/fab/images/RESN_confidential.gif',
		WT_ROOT.'themes/fab/images/RESN_locked.gif',
		WT_ROOT.'themes/fab/images/RESN_none.gif',
		WT_ROOT.'themes/fab/images/RESN_privacy.gif',
		WT_ROOT.'themes/fab/images/silhouette_female.gif',
		WT_ROOT.'themes/fab/images/silhouette_male.gif',
		WT_ROOT.'themes/fab/images/silhouette_unknown.gif',
		WT_ROOT.'themes/fab/images/terrasrv.gif',
		WT_ROOT.'themes/fab/images/timelineChunk.gif',
		WT_ROOT.'themes/minimal/images/lsdnarrow.gif',
		WT_ROOT.'themes/minimal/images/lsltarrow.gif',
		WT_ROOT.'themes/minimal/images/lsrtarrow.gif',
		WT_ROOT.'themes/minimal/images/lsuparrow.gif',
		WT_ROOT.'themes/minimal/images/silhouette_female.gif',
		WT_ROOT.'themes/minimal/images/silhouette_male.gif',
		WT_ROOT.'themes/minimal/images/silhouette_unknown.gif',
		WT_ROOT.'themes/webtrees/images/lsdnarrow.png',
		WT_ROOT.'themes/webtrees/images/lsltarrow.png',
		WT_ROOT.'themes/webtrees/images/lsrtarrow.png',
		WT_ROOT.'themes/webtrees/images/lsuparrow.png',
		WT_ROOT.'themes/xenea/images/add.gif',
		WT_ROOT.'themes/xenea/images/admin.gif',
		WT_ROOT.'themes/xenea/images/ancestry.gif',
		WT_ROOT.'themes/xenea/images/barra.gif',
		WT_ROOT.'themes/xenea/images/buttons/addmedia.gif',
		WT_ROOT.'themes/xenea/images/buttons/addnote.gif',
		WT_ROOT.'themes/xenea/images/buttons/addrepository.gif',
		WT_ROOT.'themes/xenea/images/buttons/addsource.gif',
		WT_ROOT.'themes/xenea/images/buttons/autocomplete.gif',
		WT_ROOT.'themes/xenea/images/buttons/calendar.gif',
		WT_ROOT.'themes/xenea/images/buttons/family.gif',
		WT_ROOT.'themes/xenea/images/buttons/head.gif',
		WT_ROOT.'themes/xenea/images/buttons/indi.gif',
		WT_ROOT.'themes/xenea/images/buttons/keyboard.gif',
		WT_ROOT.'themes/xenea/images/buttons/media.gif',
		WT_ROOT.'themes/xenea/images/buttons/note.gif',
		WT_ROOT.'themes/xenea/images/buttons/place.gif',
		WT_ROOT.'themes/xenea/images/buttons/repository.gif',
		WT_ROOT.'themes/xenea/images/buttons/source.gif',
		WT_ROOT.'themes/xenea/images/buttons/target.gif',
		WT_ROOT.'themes/xenea/images/cabeza.jpg',
		WT_ROOT.'themes/xenea/images/cabeza_rtl.jpg',
		WT_ROOT.'themes/xenea/images/calendar.gif',
		WT_ROOT.'themes/xenea/images/cfamily.gif',
		WT_ROOT.'themes/xenea/images/childless.gif',
		WT_ROOT.'themes/xenea/images/children.gif',
		WT_ROOT.'themes/xenea/images/clippings.gif',
		WT_ROOT.'themes/xenea/images/darrow2.gif',
		WT_ROOT.'themes/xenea/images/darrow.gif',
		WT_ROOT.'themes/xenea/images/ddarrow.gif',
		WT_ROOT.'themes/xenea/images/descendancy.gif',
		WT_ROOT.'themes/xenea/images/dline2.gif',
		WT_ROOT.'themes/xenea/images/dline.gif',
		WT_ROOT.'themes/xenea/images/edit_fam.gif',
		WT_ROOT.'themes/xenea/images/edit_indi.gif',
		WT_ROOT.'themes/xenea/images/edit_repo.gif',
		WT_ROOT.'themes/xenea/images/edit_sour.gif',
		WT_ROOT.'themes/xenea/images/fambook.gif',
		WT_ROOT.'themes/xenea/images/fanchart.gif',
		WT_ROOT.'themes/xenea/images/gedcom.gif',
		WT_ROOT.'themes/xenea/images/help.gif',
		WT_ROOT.'themes/xenea/images/hline.gif',
		WT_ROOT.'themes/xenea/images/home.gif',
		WT_ROOT.'themes/xenea/images/hourglass.gif',
		WT_ROOT.'themes/xenea/images/indis.gif',
		WT_ROOT.'themes/xenea/images/larrow2.gif',
		WT_ROOT.'themes/xenea/images/larrow.gif',
		WT_ROOT.'themes/xenea/images/ldarrow.gif',
		WT_ROOT.'themes/xenea/images/lists.gif',
		WT_ROOT.'themes/xenea/images/lsdnarrow.gif',
		WT_ROOT.'themes/xenea/images/lsltarrow.gif',
		WT_ROOT.'themes/xenea/images/lsrtarrow.gif',
		WT_ROOT.'themes/xenea/images/lsuparrow.gif',
		WT_ROOT.'themes/xenea/images/media/doc.gif',
		WT_ROOT.'themes/xenea/images/media/ged.gif',
		WT_ROOT.'themes/xenea/images/media.gif',
		WT_ROOT.'themes/xenea/images/media/html.gif',
		WT_ROOT.'themes/xenea/images/media/pdf.gif',
		WT_ROOT.'themes/xenea/images/media/tex.gif',
		WT_ROOT.'themes/xenea/images/menu_gallery.gif',
		WT_ROOT.'themes/xenea/images/menu_help.gif',
		WT_ROOT.'themes/xenea/images/menu_media.gif',
		WT_ROOT.'themes/xenea/images/menu_note.gif',
		WT_ROOT.'themes/xenea/images/menu_punbb.gif',
		WT_ROOT.'themes/xenea/images/menu_repository.gif',
		WT_ROOT.'themes/xenea/images/menu_research.gif',
		WT_ROOT.'themes/xenea/images/menu_source.gif',
		WT_ROOT.'themes/xenea/images/minus.gif',
		WT_ROOT.'themes/xenea/images/move.gif',
		WT_ROOT.'themes/xenea/images/mypage.gif',
		WT_ROOT.'themes/xenea/images/notes.gif',
		WT_ROOT.'themes/xenea/images/patriarch.gif',
		WT_ROOT.'themes/xenea/images/pedigree.gif',
		WT_ROOT.'themes/xenea/images/place.gif',
		WT_ROOT.'themes/xenea/images/plus.gif',
		WT_ROOT.'themes/xenea/images/puntos2.gif',
		WT_ROOT.'themes/xenea/images/puntos.gif',
		WT_ROOT.'themes/xenea/images/rarrow2.gif',
		WT_ROOT.'themes/xenea/images/rarrow.gif',
		WT_ROOT.'themes/xenea/images/rdarrow.gif',
		WT_ROOT.'themes/xenea/images/relationship.gif',
		WT_ROOT.'themes/xenea/images/reminder.gif',
		WT_ROOT.'themes/xenea/images/report.gif',
		WT_ROOT.'themes/xenea/images/repository.gif',
		WT_ROOT.'themes/xenea/images/rings.gif',
		WT_ROOT.'themes/xenea/images/search.gif',
		WT_ROOT.'themes/xenea/images/sex_f_15x15.gif',
		WT_ROOT.'themes/xenea/images/sex_f_9x9.gif',
		WT_ROOT.'themes/xenea/images/sex_m_15x15.gif',
		WT_ROOT.'themes/xenea/images/sex_m_9x9.gif',
		WT_ROOT.'themes/xenea/images/sex_u_15x15.gif',
		WT_ROOT.'themes/xenea/images/sex_u_9x9.gif',
		WT_ROOT.'themes/xenea/images/sfamily.gif',
		WT_ROOT.'themes/xenea/images/silhouette_female.gif',
		WT_ROOT.'themes/xenea/images/silhouette_male.gif',
		WT_ROOT.'themes/xenea/images/silhouette_unknown.gif',
		WT_ROOT.'themes/xenea/images/sombra.gif',
		WT_ROOT.'themes/xenea/images/source.gif',
		WT_ROOT.'themes/xenea/images/spacer.gif',
		WT_ROOT.'themes/xenea/images/statistic.gif',
		WT_ROOT.'themes/xenea/images/stop.gif',
		WT_ROOT.'themes/xenea/images/timeline.gif',
		WT_ROOT.'themes/xenea/images/tree.gif',
		WT_ROOT.'themes/xenea/images/uarrow2.gif',
		WT_ROOT.'themes/xenea/images/uarrow3.gif',
		WT_ROOT.'themes/xenea/images/uarrow.gif',
		WT_ROOT.'themes/xenea/images/udarrow.gif',
		WT_ROOT.'themes/xenea/images/vline.gif',
		WT_ROOT.'themes/xenea/images/warning.gif',
		WT_ROOT.'themes/xenea/images/zoomin.gif',
		WT_ROOT.'themes/xenea/images/zoomout.gif',
		WT_ROOT.'treenav.php',
		// Removed in 1.2.0
		WT_ROOT.'themes/clouds/images/close.png',
		// WT_ROOT.'themes/clouds/images/copy.png', // Added back in 1.2.4
		WT_ROOT.'themes/clouds/images/jquery',
		WT_ROOT.'themes/clouds/images/left1G.gif',
		WT_ROOT.'themes/clouds/images/left1R.gif',
		WT_ROOT.'themes/clouds/images/left4.gif',
		WT_ROOT.'themes/clouds/images/left5.gif',
		WT_ROOT.'themes/clouds/images/left6.gif',
		WT_ROOT.'themes/clouds/images/left7.gif',
		WT_ROOT.'themes/clouds/images/left8.gif',
		WT_ROOT.'themes/clouds/images/left9.gif',
		WT_ROOT.'themes/clouds/images/open.png',
		WT_ROOT.'themes/clouds/images/pin-in.png',
		WT_ROOT.'themes/clouds/images/pin-out.png',
		WT_ROOT.'themes/clouds/images/pixel.gif',
		WT_ROOT.'themes/clouds/images/puntos2.gif',
		WT_ROOT.'themes/clouds/images/puntos.gif',
		WT_ROOT.'themes/clouds/images/right1G.gif',
		WT_ROOT.'themes/clouds/images/right1R.gif',
		WT_ROOT.'themes/clouds/images/sombra.gif',
		WT_ROOT.'themes/clouds/images/th_5.gif',
		WT_ROOT.'themes/clouds/images/th_c4.gif',
		WT_ROOT.'themes/clouds/images/w_22.png',
		WT_ROOT.'themes/clouds/jquery',
		WT_ROOT.'themes/colors/images/close.png',
		WT_ROOT.'themes/colors/images/jquery',
		WT_ROOT.'themes/colors/images/left1G.gif',
		WT_ROOT.'themes/colors/images/left1R.gif',
		WT_ROOT.'themes/colors/images/left4.gif',
		WT_ROOT.'themes/colors/images/left5.gif',
		WT_ROOT.'themes/colors/images/left6.gif',
		WT_ROOT.'themes/colors/images/left7.gif',
		WT_ROOT.'themes/colors/images/left8.gif',
		WT_ROOT.'themes/colors/images/left9.gif',
		WT_ROOT.'themes/colors/images/open.png',
		WT_ROOT.'themes/colors/images/pin-in.png',
		WT_ROOT.'themes/colors/images/pin-out.png',
		WT_ROOT.'themes/colors/images/pixel.gif',
		WT_ROOT.'themes/colors/images/puntos2.gif',
		WT_ROOT.'themes/colors/images/puntos.gif',
		WT_ROOT.'themes/colors/images/right1G.gif',
		WT_ROOT.'themes/colors/images/right1R.gif',
		WT_ROOT.'themes/colors/images/sombra.gif',
		WT_ROOT.'themes/colors/images/w_22.png',
		WT_ROOT.'themes/colors/jquery',
		WT_ROOT.'themes/fab/images/copy.png',
		WT_ROOT.'themes/fab/images/delete.png',
		WT_ROOT.'themes/fab/images/jquery',
		WT_ROOT.'themes/fab/jquery',
		WT_ROOT.'themes/minimal/images/close.png',
		WT_ROOT.'themes/minimal/images/jquery',
		WT_ROOT.'themes/minimal/images/open.png',
		WT_ROOT.'themes/minimal/images/pin-in.png',
		WT_ROOT.'themes/minimal/images/pin-out.png',
		WT_ROOT.'themes/minimal/jquery',
		WT_ROOT.'themes/webtrees/images/close.png',
		WT_ROOT.'themes/webtrees/images/copy.png',
		WT_ROOT.'themes/webtrees/images/delete.png',
		WT_ROOT.'themes/webtrees/images/jquery',
		WT_ROOT.'themes/webtrees/images/open.png',
		WT_ROOT.'themes/webtrees/images/pin-in.png',
		WT_ROOT.'themes/webtrees/images/pin-out.png',
		WT_ROOT.'themes/webtrees/jquery',
		WT_ROOT.'themes/xenea/images/close.png',
		WT_ROOT.'themes/xenea/images/copy.png',
		WT_ROOT.'themes/xenea/images/jquery',
		WT_ROOT.'themes/xenea/images/open.png',
		WT_ROOT.'themes/xenea/images/pin-in.png',
		WT_ROOT.'themes/xenea/images/pin-out.png',
		WT_ROOT.'themes/xenea/jquery',
		// Removed in 1.2.1
		// Removed in 1.2.2
		WT_ROOT.'themes/clouds/chrome.css',
		WT_ROOT.'themes/clouds/images/ancestry.gif',
		WT_ROOT.'themes/clouds/images/calendar.gif',
		WT_ROOT.'themes/clouds/images/charts.gif',
		WT_ROOT.'themes/clouds/images/descendancy.gif',
		WT_ROOT.'themes/clouds/images/edit_fam.gif',
		WT_ROOT.'themes/clouds/images/edit_media.gif',
		WT_ROOT.'themes/clouds/images/edit_note.gif',
		WT_ROOT.'themes/clouds/images/edit_repo.gif',
		WT_ROOT.'themes/clouds/images/edit_sm.png',
		WT_ROOT.'themes/clouds/images/edit_sour.gif',
		WT_ROOT.'themes/clouds/images/fambook.gif',
		WT_ROOT.'themes/clouds/images/fanchart.gif',
		WT_ROOT.'themes/clouds/images/gedcom.gif',
		WT_ROOT.'themes/clouds/images/home.gif',
		WT_ROOT.'themes/clouds/images/hourglass.gif',
		WT_ROOT.'themes/clouds/images/indi_sprite.png',
		WT_ROOT.'themes/clouds/images/menu_source.gif',
		WT_ROOT.'themes/clouds/images/search.gif',
		WT_ROOT.'themes/clouds/opera.css',
		WT_ROOT.'themes/clouds/print.css',
		WT_ROOT.'themes/clouds/style_rtl.css',
		WT_ROOT.'themes/colors/chrome.css',
		WT_ROOT.'themes/colors/css/common.css',
		WT_ROOT.'themes/colors/images/ancestry.gif',
		WT_ROOT.'themes/colors/images/buttons/addmedia.gif',
		WT_ROOT.'themes/colors/images/buttons/addnote.gif',
		WT_ROOT.'themes/colors/images/buttons/addrepository.gif',
		WT_ROOT.'themes/colors/images/buttons/addsource.gif',
		WT_ROOT.'themes/colors/images/buttons/autocomplete.gif',
		WT_ROOT.'themes/colors/images/buttons/calendar.gif',
		WT_ROOT.'themes/colors/images/buttons/family.gif',
		//WT_ROOT.'themes/colors/images/buttons/find_facts.png', // Added back in 1.2.4
		WT_ROOT.'themes/colors/images/buttons/head.gif',
		WT_ROOT.'themes/colors/images/buttons/indi.gif',
		WT_ROOT.'themes/colors/images/buttons/keyboard.gif',
		WT_ROOT.'themes/colors/images/buttons/media.gif',
		WT_ROOT.'themes/colors/images/buttons/note.gif',
		WT_ROOT.'themes/colors/images/buttons/place.gif',
		WT_ROOT.'themes/colors/images/buttons/repository.gif',
		WT_ROOT.'themes/colors/images/buttons/source.gif',
		WT_ROOT.'themes/colors/images/buttons/target.gif',
		WT_ROOT.'themes/colors/images/calendar.gif',
		WT_ROOT.'themes/colors/images/cfamily.gif',
		WT_ROOT.'themes/colors/images/charts.gif',
		WT_ROOT.'themes/colors/images/descendancy.gif',
		WT_ROOT.'themes/colors/images/edit_fam.gif',
		WT_ROOT.'themes/colors/images/edit_media.gif',
		WT_ROOT.'themes/colors/images/edit_note.gif',
		WT_ROOT.'themes/colors/images/edit_repo.gif',
		WT_ROOT.'themes/colors/images/edit_sm.png',
		WT_ROOT.'themes/colors/images/edit_sour.gif',
		WT_ROOT.'themes/colors/images/fambook.gif',
		WT_ROOT.'themes/colors/images/fanchart.gif',
		WT_ROOT.'themes/colors/images/gedcom.gif',
		WT_ROOT.'themes/colors/images/home.gif',
		WT_ROOT.'themes/colors/images/hourglass.gif',
		WT_ROOT.'themes/colors/images/indis.gif',
		WT_ROOT.'themes/colors/images/indi_sprite.png',
		WT_ROOT.'themes/colors/images/itree.gif',
		WT_ROOT.'themes/colors/images/left1B.gif',
		WT_ROOT.'themes/colors/images/left2.gif',
		WT_ROOT.'themes/colors/images/left3.gif',
		WT_ROOT.'themes/colors/images/li.gif',
		WT_ROOT.'themes/colors/images/lists.gif',
		WT_ROOT.'themes/colors/images/media/doc.gif',
		WT_ROOT.'themes/colors/images/media/ged.gif',
		WT_ROOT.'themes/colors/images/media/html.gif',
		WT_ROOT.'themes/colors/images/media/pdf.gif',
		WT_ROOT.'themes/colors/images/media/tex.gif',
		WT_ROOT.'themes/colors/images/menu_help.gif',
		WT_ROOT.'themes/colors/images/menu_note.gif',
		WT_ROOT.'themes/colors/images/menu_source.gif',
		WT_ROOT.'themes/colors/images/patriarch.gif',
		WT_ROOT.'themes/colors/images/place.gif',
		WT_ROOT.'themes/colors/images/relationship.gif',
		WT_ROOT.'themes/colors/images/right1B.gif',
		WT_ROOT.'themes/colors/images/right3.gif',
		WT_ROOT.'themes/colors/images/search.gif',
		WT_ROOT.'themes/colors/images/sfamily.gif',
		WT_ROOT.'themes/colors/images/source.gif',
		WT_ROOT.'themes/colors/images/statistic.gif',
		WT_ROOT.'themes/colors/images/timeline.gif',
		WT_ROOT.'themes/colors/images/wiki.png',
		WT_ROOT.'themes/colors/opera.css',
		WT_ROOT.'themes/colors/print.css',
		WT_ROOT.'themes/colors/style_rtl.css',
		WT_ROOT.'themes/fab/chrome.css',
		WT_ROOT.'themes/fab/opera.css',
		WT_ROOT.'themes/minimal/chrome.css',
		WT_ROOT.'themes/minimal/opera.css',
		WT_ROOT.'themes/minimal/print.css',
		WT_ROOT.'themes/minimal/style_rtl.css',
		WT_ROOT.'themes/webtrees/images/calendar.png',
		WT_ROOT.'themes/webtrees/images/charts.png',
		WT_ROOT.'themes/webtrees/images/edit_fam.png',
		WT_ROOT.'themes/webtrees/images/edit_media.png',
		WT_ROOT.'themes/webtrees/images/edit_note.png',
		WT_ROOT.'themes/webtrees/images/edit_repo.png',
		WT_ROOT.'themes/webtrees/images/edit_source.png',
		WT_ROOT.'themes/webtrees/images/help.png',
		WT_ROOT.'themes/webtrees/images/home.png',
		WT_ROOT.'themes/webtrees/images/lists.png',
		WT_ROOT.'themes/webtrees/images/reports.png',
		WT_ROOT.'themes/xenea/chrome.css',
		WT_ROOT.'themes/xenea/images/facts/ADDR.gif',
		WT_ROOT.'themes/xenea/images/facts/BAPM.gif',
		WT_ROOT.'themes/xenea/images/facts/BIRT.gif',
		WT_ROOT.'themes/xenea/images/facts/BURI.gif',
		WT_ROOT.'themes/xenea/images/facts/CEME.gif',
		WT_ROOT.'themes/xenea/images/facts/CHAN.gif',
		WT_ROOT.'themes/xenea/images/facts/CHR.gif',
		WT_ROOT.'themes/xenea/images/facts/DEAT.gif',
		WT_ROOT.'themes/xenea/images/facts/EDUC.gif',
		WT_ROOT.'themes/xenea/images/facts/ENGA.gif',
		WT_ROOT.'themes/xenea/images/facts/GRAD.gif',
		WT_ROOT.'themes/xenea/images/facts/MARR.gif',
		WT_ROOT.'themes/xenea/images/facts/_MDCL.if',
		WT_ROOT.'themes/xenea/images/facts/_MILI.gif',
		WT_ROOT.'themes/xenea/images/facts/OCCU.gif',
		WT_ROOT.'themes/xenea/images/facts/ORDN.gif',
		WT_ROOT.'themes/xenea/images/facts/PHON.gif',
		WT_ROOT.'themes/xenea/images/facts/RELA.gif',
		WT_ROOT.'themes/xenea/images/facts/RESI.gif',
		WT_ROOT.'themes/xenea/opera.css',
		WT_ROOT.'themes/xenea/print.css',
		WT_ROOT.'themes/xenea/style_rtl.css',
		// Removed in 1.2.3
		//WT_ROOT.'modules_v2', // Do not delete - users may have stored custom modules/data here
		// Removed in 1.2.4
		WT_ROOT.'includes/cssparser.inc.php',
		WT_ROOT.'js/strings.js',
		WT_ROOT.'modules_v3/gedcom_favorites/help_text.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_3_find.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_3_search_add.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_5_input.js',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_5_input.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_7_parse_addLinksTbl.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_query_1a.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_query_2a.php',
		WT_ROOT.'modules_v3/GEDFact_assistant/_MEDIA/media_query_3a.php',
		WT_ROOT.'modules_v3/lightbox/css/album_page_RTL2.css',
		WT_ROOT.'modules_v3/lightbox/css/album_page_RTL.css',
		WT_ROOT.'modules_v3/lightbox/css/album_page_RTL_ff.css',
		WT_ROOT.'modules_v3/lightbox/css/clearbox_music.css',
		WT_ROOT.'modules_v3/lightbox/css/clearbox_music_RTL.css',
		WT_ROOT.'modules_v3/user_favorites/db_schema',
		WT_ROOT.'modules_v3/user_favorites/help_text.php',
		WT_ROOT.'search_engine.php',
		WT_ROOT.'themes/_administration/images/darrow2.gif',
		WT_ROOT.'themes/_administration/images/darrow.gif',
		WT_ROOT.'themes/_administration/images/ddarrow.gif',
		WT_ROOT.'themes/_administration/images/family.gif',
		WT_ROOT.'themes/_administration/images/indi.gif',
		WT_ROOT.'themes/_administration/images/larrow2.gif',
		WT_ROOT.'themes/_administration/images/larrow.gif',
		WT_ROOT.'themes/_administration/images/ldarrow.gif',
		WT_ROOT.'themes/_administration/images/media.gif',
		WT_ROOT.'themes/_administration/images/note.gif',
		WT_ROOT.'themes/_administration/images/rarrow2.gif',
		WT_ROOT.'themes/_administration/images/rarrow.gif',
		WT_ROOT.'themes/_administration/images/rdarrow.gif',
		WT_ROOT.'themes/_administration/images/repository.gif',
		WT_ROOT.'themes/_administration/images/sex_f_9x9.gif',
		WT_ROOT.'themes/_administration/images/sex_m_9x9.gif',
		WT_ROOT.'themes/_administration/images/sex_u_9x9.gif',
		WT_ROOT.'themes/_administration/images/source.gif',
		WT_ROOT.'themes/_administration/images/trashcan.png',
		WT_ROOT.'themes/_administration/images/uarrow2.gif',
		WT_ROOT.'themes/_administration/images/uarrow.gif',
		WT_ROOT.'themes/_administration/images/udarrow.gif',
		WT_ROOT.'themes/clouds/images/add.gif',
		WT_ROOT.'themes/clouds/images/admin.gif',
		WT_ROOT.'themes/clouds/images/buttons/addmedia.gif',
		WT_ROOT.'themes/clouds/images/buttons/addnote.gif',
		WT_ROOT.'themes/clouds/images/buttons/addrepository.gif',
		WT_ROOT.'themes/clouds/images/buttons/addsource.gif',
		WT_ROOT.'themes/clouds/images/buttons/autocomplete.gif',
		WT_ROOT.'themes/clouds/images/buttons/calendar.gif',
		WT_ROOT.'themes/clouds/images/buttons/family.gif',
		WT_ROOT.'themes/clouds/images/buttons/head.gif',
		WT_ROOT.'themes/clouds/images/buttons/indi.gif',
		WT_ROOT.'themes/clouds/images/buttons/keyboard.gif',
		WT_ROOT.'themes/clouds/images/buttons/media.gif',
		WT_ROOT.'themes/clouds/images/buttons/note.gif',
		WT_ROOT.'themes/clouds/images/buttons/place.gif',
		WT_ROOT.'themes/clouds/images/buttons/repository.gif',
		WT_ROOT.'themes/clouds/images/buttons/source.gif',
		WT_ROOT.'themes/clouds/images/buttons/target.gif',
		WT_ROOT.'themes/clouds/images/center.gif',
		WT_ROOT.'themes/clouds/images/cfamily.gif',
		WT_ROOT.'themes/clouds/images/childless.gif',
		WT_ROOT.'themes/clouds/images/children.gif',
		WT_ROOT.'themes/clouds/images/clippings.gif',
		WT_ROOT.'themes/clouds/images/clouds.gif',
		WT_ROOT.'themes/clouds/images/darrow2.gif',
		WT_ROOT.'themes/clouds/images/darrow.gif',
		WT_ROOT.'themes/clouds/images/ddarrow.gif',
		WT_ROOT.'themes/clouds/images/dline2.gif',
		WT_ROOT.'themes/clouds/images/dline.gif',
		WT_ROOT.'themes/clouds/images/edit_indi.gif',
		WT_ROOT.'themes/clouds/images/favorites.gif',
		WT_ROOT.'themes/clouds/images/fscreen.gif',
		WT_ROOT.'themes/clouds/images/go.gif',
		WT_ROOT.'themes/clouds/images/help.gif',
		WT_ROOT.'themes/clouds/images/hline.gif',
		WT_ROOT.'themes/clouds/images/indis.gif',
		WT_ROOT.'themes/clouds/images/itree.gif',
		WT_ROOT.'themes/clouds/images/larrow2.gif',
		WT_ROOT.'themes/clouds/images/larrow.gif',
		WT_ROOT.'themes/clouds/images/ldarrow.gif',
		WT_ROOT.'themes/clouds/images/left1B.gif',
		WT_ROOT.'themes/clouds/images/left2.gif',
		WT_ROOT.'themes/clouds/images/left3.gif',
		WT_ROOT.'themes/clouds/images/li.gif',
		WT_ROOT.'themes/clouds/images/lists.gif',
		WT_ROOT.'themes/clouds/images/media/doc.gif',
		WT_ROOT.'themes/clouds/images/media/ged.gif',
		WT_ROOT.'themes/clouds/images/media.gif',
		WT_ROOT.'themes/clouds/images/media/html.gif',
		WT_ROOT.'themes/clouds/images/media/pdf.gif',
		WT_ROOT.'themes/clouds/images/media/tex.gif',
		WT_ROOT.'themes/clouds/images/menu_help.gif',
		WT_ROOT.'themes/clouds/images/menu_media.gif',
		WT_ROOT.'themes/clouds/images/menu_note.gif',
		WT_ROOT.'themes/clouds/images/menu_repository.gif',
		WT_ROOT.'themes/clouds/images/minus.gif',
		WT_ROOT.'themes/clouds/images/move.gif',
		WT_ROOT.'themes/clouds/images/mypage.gif',
		WT_ROOT.'themes/clouds/images/notes.gif',
		WT_ROOT.'themes/clouds/images/patriarch.gif',
		WT_ROOT.'themes/clouds/images/pedigree.gif',
		WT_ROOT.'themes/clouds/images/place.gif',
		WT_ROOT.'themes/clouds/images/plus.gif',
		WT_ROOT.'themes/clouds/images/rarrow2.gif',
		WT_ROOT.'themes/clouds/images/rarrow.gif',
		WT_ROOT.'themes/clouds/images/rdarrow.gif',
		WT_ROOT.'themes/clouds/images/readme.txt',
		WT_ROOT.'themes/clouds/images/relationship.gif',
		WT_ROOT.'themes/clouds/images/reminder.gif',
		WT_ROOT.'themes/clouds/images/remove.gif',
		WT_ROOT.'themes/clouds/images/report.gif',
		WT_ROOT.'themes/clouds/images/repository.gif',
		WT_ROOT.'themes/clouds/images/right1B.gif',
		WT_ROOT.'themes/clouds/images/right3.gif',
		WT_ROOT.'themes/clouds/images/rings.gif',
		WT_ROOT.'themes/clouds/images/sex_f_15x15.gif',
		WT_ROOT.'themes/clouds/images/sex_f_9x9.gif',
		WT_ROOT.'themes/clouds/images/sex_m_15x15.gif',
		WT_ROOT.'themes/clouds/images/sex_m_9x9.gif',
		WT_ROOT.'themes/clouds/images/sex_u_15x15.gif',
		WT_ROOT.'themes/clouds/images/sex_u_9x9.gif',
		WT_ROOT.'themes/clouds/images/sfamily.gif',
		WT_ROOT.'themes/clouds/images/source.gif',
		WT_ROOT.'themes/clouds/images/spacer.gif',
		WT_ROOT.'themes/clouds/images/statistic.gif',
		WT_ROOT.'themes/clouds/images/stop.gif',
		WT_ROOT.'themes/clouds/images/timeline.gif',
		WT_ROOT.'themes/clouds/images/uarrow2.gif',
		WT_ROOT.'themes/clouds/images/uarrow.gif',
		WT_ROOT.'themes/clouds/images/udarrow.gif',
		WT_ROOT.'themes/clouds/images/vline.gif',
		WT_ROOT.'themes/clouds/images/warning.gif',
		WT_ROOT.'themes/clouds/images/wiki.png',
		WT_ROOT.'themes/clouds/images/zoomin.gif',
		WT_ROOT.'themes/clouds/images/zoomout.gif',
		WT_ROOT.'themes/clouds/modules.css',
		WT_ROOT.'themes/colors/images/add.gif',
		WT_ROOT.'themes/colors/images/admin.gif',
		WT_ROOT.'themes/colors/images/center.gif',
		WT_ROOT.'themes/colors/images/childless.gif',
		WT_ROOT.'themes/colors/images/children.gif',
		WT_ROOT.'themes/colors/images/clippings.gif',
		WT_ROOT.'themes/colors/images/darrow2.gif',
		WT_ROOT.'themes/colors/images/darrow.gif',
		WT_ROOT.'themes/colors/images/ddarrow.gif',
		WT_ROOT.'themes/colors/images/dline2.gif',
		WT_ROOT.'themes/colors/images/dline.gif',
		WT_ROOT.'themes/colors/images/edit_indi.gif',
		WT_ROOT.'themes/colors/images/favorites.gif',
		WT_ROOT.'themes/colors/images/fscreen.gif',
		WT_ROOT.'themes/colors/images/go.gif',
		WT_ROOT.'themes/colors/images/help.gif',
		WT_ROOT.'themes/colors/images/hline.gif',
		WT_ROOT.'themes/colors/images/larrow2.gif',
		WT_ROOT.'themes/colors/images/larrow.gif',
		WT_ROOT.'themes/colors/images/ldarrow.gif',
		WT_ROOT.'themes/colors/images/media.gif',
		WT_ROOT.'themes/colors/images/menu_media.gif',
		WT_ROOT.'themes/colors/images/menu_repository.gif',
		WT_ROOT.'themes/colors/images/minus.gif',
		WT_ROOT.'themes/colors/images/move.gif',
		WT_ROOT.'themes/colors/images/mypage.gif',
		WT_ROOT.'themes/colors/images/notes.gif',
		WT_ROOT.'themes/colors/images/pedigree.gif',
		WT_ROOT.'themes/colors/images/plus.gif',
		WT_ROOT.'themes/colors/images/rarrow2.gif',
		WT_ROOT.'themes/colors/images/rarrow.gif',
		WT_ROOT.'themes/colors/images/rdarrow.gif',
		WT_ROOT.'themes/colors/images/reminder.gif',
		WT_ROOT.'themes/colors/images/remove.gif',
		WT_ROOT.'themes/colors/images/report.gif',
		WT_ROOT.'themes/colors/images/repository.gif',
		WT_ROOT.'themes/colors/images/rings.gif',
		WT_ROOT.'themes/colors/images/sex_f_15x15.gif',
		WT_ROOT.'themes/colors/images/sex_f_9x9.gif',
		WT_ROOT.'themes/colors/images/sex_m_15x15.gif',
		WT_ROOT.'themes/colors/images/sex_m_9x9.gif',
		WT_ROOT.'themes/colors/images/sex_u_15x15.gif',
		WT_ROOT.'themes/colors/images/sex_u_9x9.gif',
		WT_ROOT.'themes/colors/images/spacer.gif',
		WT_ROOT.'themes/colors/images/stop.gif',
		WT_ROOT.'themes/colors/images/uarrow2.gif',
		WT_ROOT.'themes/colors/images/uarrow.gif',
		WT_ROOT.'themes/colors/images/udarrow.gif',
		WT_ROOT.'themes/colors/images/vline.gif',
		WT_ROOT.'themes/colors/images/warning.gif',
		WT_ROOT.'themes/colors/images/zoomin.gif',
		WT_ROOT.'themes/colors/images/zoomout.gif',
		WT_ROOT.'themes/colors/modules.css',
		WT_ROOT.'themes/fab/images/add.gif',
		WT_ROOT.'themes/fab/images/admin.gif',
		WT_ROOT.'themes/fab/images/ancestry.gif',
		WT_ROOT.'themes/fab/images/buttons/addmedia.gif',
		WT_ROOT.'themes/fab/images/buttons/addnote.gif',
		WT_ROOT.'themes/fab/images/buttons/addrepository.gif',
		WT_ROOT.'themes/fab/images/buttons/addsource.gif',
		WT_ROOT.'themes/fab/images/buttons/autocomplete.gif',
		WT_ROOT.'themes/fab/images/buttons/calendar.gif',
		WT_ROOT.'themes/fab/images/buttons/family.gif',
		WT_ROOT.'themes/fab/images/buttons/head.gif',
		WT_ROOT.'themes/fab/images/buttons/indi.gif',
		WT_ROOT.'themes/fab/images/buttons/keyboard.gif',
		WT_ROOT.'themes/fab/images/buttons/media.gif',
		WT_ROOT.'themes/fab/images/buttons/note.gif',
		WT_ROOT.'themes/fab/images/buttons/place.gif',
		WT_ROOT.'themes/fab/images/buttons/repository.gif',
		WT_ROOT.'themes/fab/images/buttons/source.gif',
		WT_ROOT.'themes/fab/images/buttons/target.gif',
		WT_ROOT.'themes/fab/images/calendar.gif',
		WT_ROOT.'themes/fab/images/center.gif',
		WT_ROOT.'themes/fab/images/cfamily.gif',
		WT_ROOT.'themes/fab/images/childless.gif',
		WT_ROOT.'themes/fab/images/children.gif',
		WT_ROOT.'themes/fab/images/clippings.gif',
		WT_ROOT.'themes/fab/images/darrow2.gif',
		WT_ROOT.'themes/fab/images/darrow.gif',
		WT_ROOT.'themes/fab/images/ddarrow.gif',
		WT_ROOT.'themes/fab/images/descendancy.gif',
		WT_ROOT.'themes/fab/images/dline2.gif',
		WT_ROOT.'themes/fab/images/dline.gif',
		WT_ROOT.'themes/fab/images/edit_fam.gif',
		WT_ROOT.'themes/fab/images/edit_indi.gif',
		WT_ROOT.'themes/fab/images/edit_repo.gif',
		WT_ROOT.'themes/fab/images/edit_sm.png',
		WT_ROOT.'themes/fab/images/edit_sour.gif',
		WT_ROOT.'themes/fab/images/fambook.gif',
		WT_ROOT.'themes/fab/images/fanchart.gif',
		WT_ROOT.'themes/fab/images/favorites.gif',
		WT_ROOT.'themes/fab/images/forbidden.gif',
		WT_ROOT.'themes/fab/images/fscreen.gif',
		WT_ROOT.'themes/fab/images/gedcom.gif',
		WT_ROOT.'themes/fab/images/help.gif',
		WT_ROOT.'themes/fab/images/hline.gif',
		WT_ROOT.'themes/fab/images/hourglass.gif',
		WT_ROOT.'themes/fab/images/indis.gif',
		WT_ROOT.'themes/fab/images/itree.gif',
		WT_ROOT.'themes/fab/images/larrow2.gif',
		WT_ROOT.'themes/fab/images/larrow.gif',
		WT_ROOT.'themes/fab/images/ldarrow.gif',
		WT_ROOT.'themes/fab/images/media/doc.gif',
		WT_ROOT.'themes/fab/images/media/ged.gif',
		WT_ROOT.'themes/fab/images/media.gif',
		WT_ROOT.'themes/fab/images/media/html.gif',
		WT_ROOT.'themes/fab/images/media/pdf.gif',
		WT_ROOT.'themes/fab/images/media/tex.gif',
		WT_ROOT.'themes/fab/images/minus.gif',
		WT_ROOT.'themes/fab/images/move.gif',
		WT_ROOT.'themes/fab/images/mypage.gif',
		WT_ROOT.'themes/fab/images/patriarch.gif',
		WT_ROOT.'themes/fab/images/pedigree.gif',
		WT_ROOT.'themes/fab/images/pix1.gif',
		WT_ROOT.'themes/fab/images/place.gif',
		WT_ROOT.'themes/fab/images/plus.gif',
		WT_ROOT.'themes/fab/images/rarrow2.gif',
		WT_ROOT.'themes/fab/images/rarrow.gif',
		WT_ROOT.'themes/fab/images/rdarrow.gif',
		WT_ROOT.'themes/fab/images/relationship.gif',
		WT_ROOT.'themes/fab/images/reminder.gif',
		WT_ROOT.'themes/fab/images/remove.gif',
		WT_ROOT.'themes/fab/images/reports.gif',
		WT_ROOT.'themes/fab/images/repository.gif',
		WT_ROOT.'themes/fab/images/rings.gif',
		WT_ROOT.'themes/fab/images/search.gif',
		WT_ROOT.'themes/fab/images/sex_f_15x15.gif',
		WT_ROOT.'themes/fab/images/sex_f_9x9.gif',
		WT_ROOT.'themes/fab/images/sex_m_15x15.gif',
		WT_ROOT.'themes/fab/images/sex_m_9x9.gif',
		WT_ROOT.'themes/fab/images/sex_u_15x15.gif',
		WT_ROOT.'themes/fab/images/sex_u_9x9.gif',
		WT_ROOT.'themes/fab/images/sfamily.gif',
		WT_ROOT.'themes/fab/images/source.gif',
		WT_ROOT.'themes/fab/images/spacer.gif',
		WT_ROOT.'themes/fab/images/statistic.gif',
		WT_ROOT.'themes/fab/images/stop.gif',
		WT_ROOT.'themes/fab/images/timeline.gif',
		WT_ROOT.'themes/fab/images/topdown.gif',
		WT_ROOT.'themes/fab/images/uarrow2.gif',
		WT_ROOT.'themes/fab/images/uarrow.gif',
		WT_ROOT.'themes/fab/images/udarrow.gif',
		WT_ROOT.'themes/fab/images/vline.gif',
		WT_ROOT.'themes/fab/images/warning.gif',
		WT_ROOT.'themes/fab/images/zoomin.gif',
		WT_ROOT.'themes/fab/images/zoomout.gif',
		WT_ROOT.'themes/fab/modules.css',
		WT_ROOT.'themes/minimal/images/add.gif',
		WT_ROOT.'themes/minimal/images/admin.gif',
		WT_ROOT.'themes/minimal/images/ancestry.gif',
		WT_ROOT.'themes/minimal/images/buttons/addmedia.gif',
		WT_ROOT.'themes/minimal/images/buttons/addnote.gif',
		WT_ROOT.'themes/minimal/images/buttons/addrepository.gif',
		WT_ROOT.'themes/minimal/images/buttons/addsource.gif',
		WT_ROOT.'themes/minimal/images/buttons/calendar.gif',
		WT_ROOT.'themes/minimal/images/buttons/family.gif',
		WT_ROOT.'themes/minimal/images/buttons/head.gif',
		WT_ROOT.'themes/minimal/images/buttons/indi.gif',
		WT_ROOT.'themes/minimal/images/buttons/keyboard.gif',
		WT_ROOT.'themes/minimal/images/buttons/media.gif',
		WT_ROOT.'themes/minimal/images/buttons/note.gif',
		WT_ROOT.'themes/minimal/images/buttons/place.gif',
		WT_ROOT.'themes/minimal/images/buttons/repository.gif',
		WT_ROOT.'themes/minimal/images/buttons/source.gif',
		WT_ROOT.'themes/minimal/images/buttons/target.gif',
		WT_ROOT.'themes/minimal/images/calendar.gif',
		WT_ROOT.'themes/minimal/images/center.gif',
		WT_ROOT.'themes/minimal/images/cfamily.gif',
		WT_ROOT.'themes/minimal/images/childless.gif',
		WT_ROOT.'themes/minimal/images/children.gif',
		WT_ROOT.'themes/minimal/images/clippings.gif',
		WT_ROOT.'themes/minimal/images/darrow2.gif',
		WT_ROOT.'themes/minimal/images/darrow.gif',
		WT_ROOT.'themes/minimal/images/ddarrow.gif',
		WT_ROOT.'themes/minimal/images/descendancy.gif',
		WT_ROOT.'themes/minimal/images/dline2.gif',
		WT_ROOT.'themes/minimal/images/dline.gif',
		WT_ROOT.'themes/minimal/images/fambook.gif',
		WT_ROOT.'themes/minimal/images/fanchart.gif',
		WT_ROOT.'themes/minimal/images/fscreen.gif',
		WT_ROOT.'themes/minimal/images/gedcom.gif',
		WT_ROOT.'themes/minimal/images/help.gif',
		WT_ROOT.'themes/minimal/images/hline.gif',
		WT_ROOT.'themes/minimal/images/indis.gif',
		WT_ROOT.'themes/minimal/images/itree.gif',
		WT_ROOT.'themes/minimal/images/larrow2.gif',
		WT_ROOT.'themes/minimal/images/larrow.gif',
		WT_ROOT.'themes/minimal/images/ldarrow.gif',
		WT_ROOT.'themes/minimal/images/media/doc.gif',
		WT_ROOT.'themes/minimal/images/media/ged.gif',
		WT_ROOT.'themes/minimal/images/media.gif',
		WT_ROOT.'themes/minimal/images/media/html.gif',
		WT_ROOT.'themes/minimal/images/media/pdf.gif',
		WT_ROOT.'themes/minimal/images/media/tex.gif',
		WT_ROOT.'themes/minimal/images/minus.gif',
		WT_ROOT.'themes/minimal/images/move.gif',
		WT_ROOT.'themes/minimal/images/mypage.gif',
		WT_ROOT.'themes/minimal/images/notes.gif',
		WT_ROOT.'themes/minimal/images/patriarch.gif',
		WT_ROOT.'themes/minimal/images/pedigree.gif',
		WT_ROOT.'themes/minimal/images/place.gif',
		WT_ROOT.'themes/minimal/images/plus.gif',
		WT_ROOT.'themes/minimal/images/rarrow2.gif',
		WT_ROOT.'themes/minimal/images/rarrow.gif',
		WT_ROOT.'themes/minimal/images/rdarrow.gif',
		WT_ROOT.'themes/minimal/images/relationship.gif',
		WT_ROOT.'themes/minimal/images/reminder.gif',
		WT_ROOT.'themes/minimal/images/remove.gif',
		WT_ROOT.'themes/minimal/images/report.gif',
		WT_ROOT.'themes/minimal/images/repository.gif',
		WT_ROOT.'themes/minimal/images/rings.gif',
		WT_ROOT.'themes/minimal/images/search.gif',
		WT_ROOT.'themes/minimal/images/sex_f_15x15.gif',
		WT_ROOT.'themes/minimal/images/sex_f_9x9.gif',
		WT_ROOT.'themes/minimal/images/sex_m_15x15.gif',
		WT_ROOT.'themes/minimal/images/sex_m_9x9.gif',
		WT_ROOT.'themes/minimal/images/sex_u_15x15.gif',
		WT_ROOT.'themes/minimal/images/sex_u_9x9.gif',
		WT_ROOT.'themes/minimal/images/sfamily.gif',
		WT_ROOT.'themes/minimal/images/source.gif',
		WT_ROOT.'themes/minimal/images/spacer.gif',
		WT_ROOT.'themes/minimal/images/stop.gif',
		WT_ROOT.'themes/minimal/images/timeline.gif',
		WT_ROOT.'themes/minimal/images/uarrow2.gif',
		WT_ROOT.'themes/minimal/images/uarrow.gif',
		WT_ROOT.'themes/minimal/images/udarrow.gif',
		WT_ROOT.'themes/minimal/images/vline.gif',
		WT_ROOT.'themes/minimal/images/warning.gif',
		WT_ROOT.'themes/minimal/images/zoomin.gif',
		WT_ROOT.'themes/minimal/images/zoomout.gif',
		WT_ROOT.'themes/minimal/modules.css',
		WT_ROOT.'themes/webtrees/images/center.gif',
		WT_ROOT.'themes/webtrees/images/fscreen.gif',
		WT_ROOT.'themes/webtrees/modules.css',
		WT_ROOT.'themes/xenea/images/center.gif',
		WT_ROOT.'themes/xenea/images/fscreen.gif',
		WT_ROOT.'themes/xenea/images/pixel.gif',
		WT_ROOT.'themes/xenea/images/remove.gif',
		WT_ROOT.'themes/xenea/modules.css',
		// Removed in 1.2.5
		WT_ROOT.'includes/media_reorder_count.php',
		WT_ROOT.'includes/media_tab_head.php',
		WT_ROOT.'js/behaviour.js.htm',
		WT_ROOT.'js/bennolan',
		WT_ROOT.'js/bosrup',
		WT_ROOT.'js/kryogenix',
		WT_ROOT.'js/overlib.js.htm',
		WT_ROOT.'js/scriptaculous',
		WT_ROOT.'js/scriptaculous.js.htm',
		WT_ROOT.'js/sorttable.js.htm',
		WT_ROOT.'library/WT/JS.php',
		WT_ROOT.'modules_v3/clippings/index.php',
		WT_ROOT.'modules_v3/googlemap/css/googlemap_style.css',
		WT_ROOT.'modules_v3/googlemap/css/wt_v3_places_edit.css',
		WT_ROOT.'modules_v3/googlemap/index.php',
		WT_ROOT.'modules_v3/lightbox/index.php',
		WT_ROOT.'modules_v3/recent_changes/help_text.php',
		WT_ROOT.'modules_v3/todays_events/help_text.php',
		WT_ROOT.'sidebar.php',
		// Removed in 1.2.6
		WT_ROOT.'modules_v3/sitemap/admin_index.php',
		WT_ROOT.'modules_v3/sitemap/help_text.php',
		WT_ROOT.'modules_v3/tree/css/styles',
		WT_ROOT.'modules_v3/tree/css/treebottom.gif',
		WT_ROOT.'modules_v3/tree/css/treebottomleft.gif',
		WT_ROOT.'modules_v3/tree/css/treebottomright.gif',
		WT_ROOT.'modules_v3/tree/css/tree.jpg',
		WT_ROOT.'modules_v3/tree/css/treeleft.gif',
		WT_ROOT.'modules_v3/tree/css/treeright.gif',
		WT_ROOT.'modules_v3/tree/css/treetop.gif',
		WT_ROOT.'modules_v3/tree/css/treetopleft.gif',
		WT_ROOT.'modules_v3/tree/css/treetopright.gif',
		WT_ROOT.'modules_v3/tree/css/treeview_print.css',
		WT_ROOT.'modules_v3/tree/help_text.php',
		WT_ROOT.'modules_v3/tree/images/print.png',
		WT_ROOT.'themes/clouds/images/fscreen.png',
		WT_ROOT.'themes/colors/images/fscreen.png',
		WT_ROOT.'themes/fab/images/fscreen.png',
		WT_ROOT.'themes/minimal/images/fscreen.png',
		WT_ROOT.'themes/webtrees/images/fscreen.png',
		WT_ROOT.'themes/xenea/images/fscreen.png',		
		// Removed in 1.2.7
		WT_ROOT.'login_register.php',
		WT_ROOT.'modules_v3/top10_givnnames/help_text.php',
		WT_ROOT.'modules_v3/top10_surnames/help_text.php',
		WT_ROOT.'themes/clouds/images/center.png',
		WT_ROOT.'themes/colors/images/center.png',
		WT_ROOT.'themes/fab/images/center.png',
		WT_ROOT.'themes/minimal/images/center.png',
		WT_ROOT.'themes/webtrees/images/center.png',
		WT_ROOT.'themes/xenea/images/center.png',
		// Removed in 1.3.0
		WT_ROOT.'admin_site_ipaddress.php',
		WT_ROOT.'downloadgedcom.php',
		WT_ROOT.'export_gedcom.php',
		WT_ROOT.'gedcheck.php',
		WT_ROOT.'images',
		WT_ROOT.'includes/dmsounds_UTF8.php',
		WT_ROOT.'includes/functions/functions_name.php',
		WT_ROOT.'includes/grampsxml.rng',
		WT_ROOT.'includes/session_spider.php',
		WT_ROOT.'js/autocomplete.js.htm',
		WT_ROOT.'js/prototype',
		WT_ROOT.'js/prototype.js.htm',
		WT_ROOT.'modules_v3/googlemap/admin_editconfig.php',
		WT_ROOT.'modules_v3/googlemap/admin_placecheck.php',
		WT_ROOT.'modules_v3/googlemap/flags.php',
		WT_ROOT.'modules_v3/googlemap/images/pedigree_map.gif',
		WT_ROOT.'modules_v3/googlemap/pedigree_map.php',
		WT_ROOT.'modules_v3/lightbox/admin_config.php',
		WT_ROOT.'modules_v3/lightbox/album.php',
		WT_ROOT.'modules_v3/lightbox/functions/lb_call_js.php',
		WT_ROOT.'modules_v3/lightbox/functions/lb_head.php',
		WT_ROOT.'modules_v3/lightbox/functions/lb_link.php',
		WT_ROOT.'modules_v3/lightbox/functions/lightbox_print_media_row.php',
		WT_ROOT.'modules_v3/tree/css/vline.jpg',
		WT_ROOT.'themes/_administration/images/darrow2.png',
		WT_ROOT.'themes/_administration/images/darrow.png',
		WT_ROOT.'themes/_administration/images/ddarrow.png',
		WT_ROOT.'themes/_administration/images/delete_grey.png',
		WT_ROOT.'themes/_administration/images/family.png',
		WT_ROOT.'themes/_administration/images/find_facts.png',
		WT_ROOT.'themes/_administration/images/header.png',
		WT_ROOT.'themes/_administration/images/help.png',
		WT_ROOT.'themes/_administration/images/indi.png',
		WT_ROOT.'themes/_administration/images/larrow2.png',
		WT_ROOT.'themes/_administration/images/larrow.png',
		WT_ROOT.'themes/_administration/images/ldarrow.png',
		WT_ROOT.'themes/_administration/images/media.png',
		WT_ROOT.'themes/_administration/images/note.png',
		WT_ROOT.'themes/_administration/images/rarrow2.png',
		WT_ROOT.'themes/_administration/images/rarrow.png',
		WT_ROOT.'themes/_administration/images/rdarrow.png',
		WT_ROOT.'themes/_administration/images/repository.png',
		WT_ROOT.'themes/_administration/images/source.png',
		WT_ROOT.'themes/_administration/images/uarrow2.png',
		WT_ROOT.'themes/_administration/images/uarrow.png',
		WT_ROOT.'themes/_administration/images/udarrow.png',
		WT_ROOT.'themes/clouds/images/favorites.png',
		WT_ROOT.'themes/clouds/images/lists.png',
		WT_ROOT.'themes/clouds/images/menu_media.png',
		WT_ROOT.'themes/clouds/images/menu_note.png',
		WT_ROOT.'themes/clouds/images/menu_repository.png',
		WT_ROOT.'themes/clouds/images/relationship.png',
		WT_ROOT.'themes/clouds/images/reorder_images.png',
		WT_ROOT.'themes/clouds/images/report.png',
		WT_ROOT.'themes/colors/images/favorites.png',
		WT_ROOT.'themes/colors/images/menu_media.png',
		WT_ROOT.'themes/colors/images/menu_note.png',
		WT_ROOT.'themes/colors/images/menu_repository.png',
		WT_ROOT.'themes/colors/images/reorder_images.png',
		WT_ROOT.'themes/fab/images/ancestry.png',
		WT_ROOT.'themes/fab/images/calendar.png',
		WT_ROOT.'themes/fab/images/descendancy.png',
		WT_ROOT.'themes/fab/images/edit_fam.png',
		WT_ROOT.'themes/fab/images/edit_repo.png',
		WT_ROOT.'themes/fab/images/edit_sour.png',
		WT_ROOT.'themes/fab/images/fanchart.png',
		WT_ROOT.'themes/fab/images/favorites.png',
		WT_ROOT.'themes/fab/images/hourglass.png',
		WT_ROOT.'themes/fab/images/itree.png',
		WT_ROOT.'themes/fab/images/relationship.png',
		WT_ROOT.'themes/fab/images/reorder_images.png',
		WT_ROOT.'themes/fab/images/reports.png',
		WT_ROOT.'themes/fab/images/statistic.png',
		WT_ROOT.'themes/fab/images/timeline.png',
		WT_ROOT.'themes/minimal/images/ancestry.png',
		WT_ROOT.'themes/minimal/images/buttons',
		WT_ROOT.'themes/minimal/images/descendancy.png',
		WT_ROOT.'themes/minimal/images/fanchart.png',
		WT_ROOT.'themes/minimal/images/itree.png',
		WT_ROOT.'themes/minimal/images/relationship.png',
		WT_ROOT.'themes/minimal/images/report.png',
		WT_ROOT.'themes/minimal/images/timeline.png',
		WT_ROOT.'themes/minimal/images/webtrees.png',
		WT_ROOT.'themes/webtrees/images/ancestry.png',
		WT_ROOT.'themes/webtrees/images/descendancy.png',
		WT_ROOT.'themes/webtrees/images/fanchart.png',
		WT_ROOT.'themes/webtrees/images/favorites.png',
		WT_ROOT.'themes/webtrees/images/hourglass.png',
		WT_ROOT.'themes/webtrees/images/media/audio.png',
		WT_ROOT.'themes/webtrees/images/media/doc.png',
		WT_ROOT.'themes/webtrees/images/media/flash.png',
		WT_ROOT.'themes/webtrees/images/media/flashrem.png',
		WT_ROOT.'themes/webtrees/images/media/pdf.png',
		WT_ROOT.'themes/webtrees/images/media/picasa.png',
		WT_ROOT.'themes/webtrees/images/media/tex.png',
		WT_ROOT.'themes/webtrees/images/media/unknown.png',
		WT_ROOT.'themes/webtrees/images/media/wmv.png',
		WT_ROOT.'themes/webtrees/images/media/wmvrem.png',
		WT_ROOT.'themes/webtrees/images/media/www.png',
		WT_ROOT.'themes/webtrees/images/relationship.png',
		WT_ROOT.'themes/webtrees/images/reorder_images.png',
		WT_ROOT.'themes/webtrees/images/statistic.png',
		WT_ROOT.'themes/webtrees/images/timeline.png',
		WT_ROOT.'themes/webtrees/images/w_22.png',
		WT_ROOT.'themes/xenea/images/ancestry.png',
		WT_ROOT.'themes/xenea/images/calendar.png',
		WT_ROOT.'themes/xenea/images/descendancy.png',
		WT_ROOT.'themes/xenea/images/edit_fam.png',
		WT_ROOT.'themes/xenea/images/edit_repo.png',
		WT_ROOT.'themes/xenea/images/edit_sour.png',
		WT_ROOT.'themes/xenea/images/fanchart.png',
		WT_ROOT.'themes/xenea/images/gedcom.png',
		WT_ROOT.'themes/xenea/images/hourglass.png',
		WT_ROOT.'themes/xenea/images/menu_help.png',
		WT_ROOT.'themes/xenea/images/menu_media.png',
		WT_ROOT.'themes/xenea/images/menu_note.png',
		WT_ROOT.'themes/xenea/images/menu_repository.png',
		WT_ROOT.'themes/xenea/images/menu_source.png',
		WT_ROOT.'themes/xenea/images/relationship.png',
		WT_ROOT.'themes/xenea/images/reorder_images.png',
		WT_ROOT.'themes/xenea/images/report.png',
		WT_ROOT.'themes/xenea/images/statistic.png',
		WT_ROOT.'themes/xenea/images/timeline.png',
		WT_ROOT.'themes/xenea/images/w_22.png',
		// Removed in 1.3.1
		WT_ROOT.'imageflush.php',
		WT_ROOT.'includes/functions/functions_places.php',
		WT_ROOT.'js/html5.js',
		WT_ROOT.'modules_v3/googlemap/wt_v3_pedigree_map.js.php',
		WT_ROOT.'modules_v3/lightbox/js/tip_balloon_RTL.js',
		// Removed in 1.3.2
		WT_ROOT.'modules_v3/address_report',
		// Removed in 1.4.0
		WT_ROOT.'imageview.php',
		WT_ROOT.'includes/functions/functions_media_reorder.php',
		WT_ROOT.'js/jquery',
		WT_ROOT.'js/jw_player',
		WT_ROOT.'media/MediaInfo.txt',
		WT_ROOT.'media/thumbs/ThumbsInfo.txt',
		WT_ROOT.'modules_v3/GEDFact_assistant/css/media_0_inverselink.css',
		WT_ROOT.'modules_v3/lightbox/help_text.php',
		WT_ROOT.'modules_v3/lightbox/images/blank.gif',
		WT_ROOT.'modules_v3/lightbox/images/close_1.gif',
		WT_ROOT.'modules_v3/lightbox/images/image_add.gif',
		WT_ROOT.'modules_v3/lightbox/images/image_copy.gif',
		WT_ROOT.'modules_v3/lightbox/images/image_delete.gif',
		WT_ROOT.'modules_v3/lightbox/images/image_edit.gif',
		WT_ROOT.'modules_v3/lightbox/images/image_link.gif',
		WT_ROOT.'modules_v3/lightbox/images/images.gif',
		WT_ROOT.'modules_v3/lightbox/images/image_view.gif',
		WT_ROOT.'modules_v3/lightbox/images/loading.gif',
		WT_ROOT.'modules_v3/lightbox/images/next.gif',
		WT_ROOT.'modules_v3/lightbox/images/nextlabel.gif',
		WT_ROOT.'modules_v3/lightbox/images/norm_2.gif',
		WT_ROOT.'modules_v3/lightbox/images/overlay.png',
		WT_ROOT.'modules_v3/lightbox/images/prev.gif',
		WT_ROOT.'modules_v3/lightbox/images/prevlabel.gif',
		WT_ROOT.'modules_v3/lightbox/images/private.gif',
		WT_ROOT.'modules_v3/lightbox/images/slideshow.jpg',
		WT_ROOT.'modules_v3/lightbox/images/transp80px.gif',
		WT_ROOT.'modules_v3/lightbox/images/zoom_1.gif',
		WT_ROOT.'modules_v3/lightbox/js',
		WT_ROOT.'modules_v3/lightbox/music',
		WT_ROOT.'modules_v3/lightbox/pic',
		WT_ROOT.'themes/_administration/images/media',
		WT_ROOT.'themes/_administration/jquery',
		WT_ROOT.'themes/clouds/images/media',
		WT_ROOT.'themes/colors/images/media',
		WT_ROOT.'themes/fab/images/media',
		WT_ROOT.'themes/minimal/images/media',
		WT_ROOT.'themes/webtrees/chrome.css',
		WT_ROOT.'themes/webtrees/images/media',
		WT_ROOT.'themes/xenea/images/media',
		// Removed in 1.4.1
		WT_ROOT.'js/webtrees-1.4.0.js',
		WT_ROOT.'modules_v3/lightbox/images/image_edit.png',
		WT_ROOT.'modules_v3/lightbox/images/image_view.png',
		// Removed in 1.4.2
		WT_ROOT.'modules_v3/lightbox/images/image_view.png',
		WT_ROOT.'js/jquery.colorbox-1.4.3.js',
		WT_ROOT.'js/jquery-ui-1.10.0.js',
		WT_ROOT.'js/webtrees-1.4.1.js',
		WT_ROOT.'modules_v3/top10_pageviews/help_text.php',
		WT_ROOT.'themes/_administration/jquery-ui-1.10.0',
		WT_ROOT.'themes/clouds/jquery-ui-1.10.0',
		WT_ROOT.'themes/colors/jquery-ui-1.10.0',
		WT_ROOT.'themes/fab/jquery-ui-1.10.0',
		WT_ROOT.'themes/minimal/jquery-ui-1.10.0',
		WT_ROOT.'themes/webtrees/jquery-ui-1.10.0',
		WT_ROOT.'themes/xenea/jquery-ui-1.10.0',
		// Removed in kiwitrees 2.0.1
		WT_ROOT.'modules_v3/simpl_research/plugins/findmypastuk.php',
		// Removed in kiwitrees 2.0.2
		WT_ROOT.'js/jquery-1.10.2.js',
		WT_ROOT.'js/webtrees-1.4.2.js',
		WT_ROOT.'js/jquery-ui-1.10.3.js',
		WT_ROOT.'js/jquery.wheelzoom-1.1.2.js',
		WT_ROOT.'js/jquery.jeditable-1.7.1.js',
		WT_ROOT.'js/jquery.cookie-1.4.0.js',
		WT_ROOT.'js/jquery.datatables-1.9.4.js',
		WT_ROOT.'js/jquery.colorbox-1.4.15.js',
		WT_ROOT.'js/modernizr.custom-2.6.2.js',
		WT_ROOT.'modules_v3/fancy_imagebar/style.css',
		WT_ROOT.'modules_v3/fancy_imagebar/README.md',
		WT_ROOT.'modules_v3/media',
		WT_ROOT.'modules_v3/lightbox',
		// Removed in kiwitrees 3.0.0
		WT_ROOT.'library/WT/Debug.php',
		WT_ROOT.'modules_v3/simpl_duplicates',
		WT_ROOT.'modules_v3/simpl_unlinked',
		WT_ROOT.'modules_v3/gallery/galleria/galleria-1.3.5.js',
		WT_ROOT.'modules_v3/gallery/galleria/galleria-1.3.5.min.js',
		WT_ROOT.'modules_v3/gallery/galleria/galleria-1.3.6.js',
		WT_ROOT.'modules_v3/gallery/galleria/galleria-1.3.6.min.js',
		WT_ROOT.'themes/clouds',
		WT_ROOT.'themes/fab',
		WT_ROOT.'themes/minimal',
		WT_ROOT.'themes/kiwitrees/jquery-ui-1.10.3',
	);
}

// Delete a file or folder, ignoring errors
function delete_recursively($path) {
	@chmod($path, 0777);
	if (is_dir($path)) {
		$dir=opendir($path);
		while ($dir!==false && (($file=readdir($dir))!==false)) {
			if ($file!='.' && $file!='..') {
				delete_recursively($path.'/'.$file);
			}
		}
		closedir($dir);
		@rmdir($path);
	} else {
		@unlink($path);
	}
}
