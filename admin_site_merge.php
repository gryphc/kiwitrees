<?php
// Merge Two Gedcom Records
//
// This page will allow you to merge 2 gedcom records
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010  PGV Development Team
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

define('WT_SCRIPT_NAME', 'admin_site_merge.php');
require './includes/session.php';

$controller=new WT_Controller_Page;
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Merge records'))
	->pageHeader();

require_once WT_ROOT.'includes/functions/functions_edit.php';
require_once WT_ROOT.'includes/functions/functions_import.php';

$ged=$GEDCOM;
$gid1=safe_POST_xref('gid1');
$gid2=safe_POST_xref('gid2');
$action=safe_POST('action', WT_REGEX_ALPHA, 'choose');
$ged2=safe_POST('ged2', WT_REGEX_NOSCRIPT, $GEDCOM);
$keep1=safe_POST('keep1', WT_REGEX_UNSAFE);
$keep2=safe_POST('keep2', WT_REGEX_UNSAFE);
if (empty($keep1)) $keep1=array();
if (empty($keep2)) $keep2=array();

if (count(WT_Tree::getAll())==1) { //Removed becasue it doesn't work here for multiple GEDCOMs. Can be reinstated when fixed (https://bugs.launchpad.net/webtrees/+bug/613235)
	$controller->addExternalJavascript(WT_STATIC_URL.'js/autocomplete.js');
}

if ($action!='choose') {
	if ($gid1==$gid2 && $GEDCOM==$ged2) {
		$action='choose';
		echo '<span class="error">', WT_I18N::translate('You entered the same IDs.  You cannot merge the same records.'), '</span>';
	} else {
		$gedrec1 = find_gedcom_record($gid1, WT_GED_ID, true);
		$gedrec2 = find_gedcom_record($gid2, get_id_from_gedcom($ged2), true);

		// Fetch the original XREF - may differ in case from the supplied value
		$tmp=new WT_Person($gedrec1); $gid1=$tmp->getXref();
		$tmp=new WT_Person($gedrec2); $gid2=$tmp->getXref();

		if (empty($gedrec1)) {
			echo '<span class="error">', WT_I18N::translate('Unable to find record with ID'), ':</span> ', $gid1, ', ', $ged;
			$action='choose';
		} elseif (empty($gedrec2)) {
			echo '<span class="error">', WT_I18N::translate('Unable to find record with ID'), ':</span> ', $gid2, ', ', $ged2;
			$action='choose';
		} else {
			$type1 = '';
			$ct = preg_match("/0 @$gid1@ (.*)/", $gedrec1, $match);
			if ($ct>0) {
				$type1 = trim($match[1]);
			}
			$type2 = "";
			$ct = preg_match("/0 @$gid2@ (.*)/", $gedrec2, $match);
			if ($ct>0) $type2 = trim($match[1]);
			if (!empty($type1) && ($type1!=$type2)) {
				echo '<span class="error">', WT_I18N::translate('Records are not the same type.  Cannot merge records that are not the same type.'), '</span>';
				$action='choose';
			} else {
				$facts1 = array();
				$facts2 = array();
				$prev_tags = array();
				$ct = preg_match_all('/\n1 (\w+)/', $gedrec1, $match, PREG_SET_ORDER);
				for ($i=0; $i<$ct; $i++) {
					$fact = trim($match[$i][1]);
					if (isset($prev_tags[$fact])) {
						$prev_tags[$fact]++;
					} else {
						$prev_tags[$fact] = 1;
					}
					$subrec = get_sub_record(1, "1 $fact", $gedrec1, $prev_tags[$fact]);
					$facts1[] = array('fact'=>$fact, 'subrec'=>trim($subrec));
				}
				$prev_tags = array();
				$ct = preg_match_all('/\n1 (\w+)/', $gedrec2, $match, PREG_SET_ORDER);
				for ($i=0; $i<$ct; $i++) {
					$fact = trim($match[$i][1]);
					if (isset($prev_tags[$fact])) {
						$prev_tags[$fact]++;
					} else {
						$prev_tags[$fact] = 1;
					}
					$subrec = get_sub_record(1, "1 $fact", $gedrec2, $prev_tags[$fact]);
					$facts2[] = array('fact'=>$fact, 'subrec'=>trim($subrec));
				}
				if ($action=='select') {
					echo '<div id="merge2"><h3>', WT_I18N::translate('Merge records'), '</h3>';
					echo '<form method="post" action="admin_site_merge.php">';
					echo WT_I18N::translate('The following facts were exactly the same in both records and will be merged automatically.'), '<br>';
					echo '<input type="hidden" name="gid1" value="', $gid1, '">';
					echo '<input type="hidden" name="gid2" value="', $gid2, '">';
					echo '<input type="hidden" name="ged" value="', $GEDCOM, '">';
					echo '<input type="hidden" name="ged2" value="', $ged2, '">';
					echo '<input type="hidden" name="action" value="merge">';
					$equal_count=0;
					$skip1 = array();
					$skip2 = array();
					echo '<table>';
					foreach ($facts1 as $i=>$fact1) {
						foreach ($facts2 as $j=>$fact2) {
							if (utf8_strtoupper($fact1['subrec'])==utf8_strtoupper($fact2['subrec'])) {
								$skip1[] = $i;
								$skip2[] = $j;
								$equal_count++;
								echo '<tr><td>', WT_I18N::translate($fact1['fact']);
								// PHP5.3 echo '<input type="hidden" name="keep1[]" value="', $i, '"></td><td>', nl2br($fact1['subrec'], false), '</td></tr>';
								echo '<input type="hidden" name="keep1[]" value="', $i, '"></td><td>', nl2br($fact1['subrec']), '</td></tr>';
							}
						}
					}
					if ($equal_count==0) {
						echo '<tr><td>', WT_I18N::translate('No matching facts found'), '</td></tr>';
					}
					echo '</table><br>';
					echo WT_I18N::translate('The following facts did not match.  Select the information you would like to keep.');
					echo '<table>';
					echo '<tr><th>', WT_I18N::translate('Record'), ' ', $gid1, '</th><th>', WT_I18N::translate('Record'), ' ', $gid2, '</th></tr>';
					echo '<tr><td>';
					echo '<table>';
					foreach ($facts1 as $i=>$fact1) {
						if (($fact1['fact']!='CHAN')&&(!in_array($i, $skip1))) {
							echo '<tr><td><input type="checkbox" name="keep1[]" value="', $i, '" checked="checked"></td>';
							// PHP5.3 echo '<td>', nl2br($fact1['subrec'], false), '</td></tr>';
							echo '<td>', nl2br($fact1['subrec']), '</td></tr>';
						}
					}
					echo '</table>';
					echo '</td><td>';
					echo '<table>';
					foreach ($facts2 as $j=>$fact2) {
						if (($fact2['fact']!='CHAN')&&(!in_array($j, $skip2))) {
							echo '<tr><td><input type="checkbox" name="keep2[]" value="', $j, '" checked="checked"></td>';
							// PHP5.3 echo '<td>', nl2br($fact2['subrec'], false), '</td></tr>';
							echo '<td>', nl2br($fact2['subrec']), '</td></tr>';
						}
					}
					echo '</table>';
					echo '</td></tr>';
					echo '</table>';
					echo '<input type="submit" value="', WT_I18N::translate('save'), '">';
					echo '</form></div>';
				} elseif ($action=='merge') {
					$manual_save = true;
					echo '<div id="merge3"><h3>', WT_I18N::translate('Merge records'), '</h3>';
					if ($GEDCOM==$ged2) {
						$success = delete_gedrec($gid2, WT_GED_ID);
						echo WT_I18N::translate('GEDCOM record successfully deleted.'), '<br>';
						//-- replace all the records that linked to gid2
						$ids=fetch_all_links($gid2, WT_GED_ID);
						foreach ($ids as $id) {
							$record=find_gedcom_record($id, WT_GED_ID, true);
							echo WT_I18N::translate('Updating linked record'), ' ', $id, '<br>';
							$newrec=str_replace("@$gid2@", "@$gid1@", $record);
							$newrec=preg_replace(
								'/(\n1.*@.+@.*(?:(?:\n[2-9].*)*))((?:\n1.*(?:\n[2-9].*)*)*\1)/',
								'$2',
								$newrec
							);
							replace_gedrec($id, WT_GED_ID, $newrec);
						}
						// Update any linked user-accounts
						WT_DB::prepare(
							"UPDATE `##user_gedcom_setting`".
							" SET setting_value=?".
							" WHERE gedcom_id=? AND setting_name='gedcomid' AND setting_value=?"
						)->execute(array($gid2, WT_GED_ID, $gid1));

						// Merge hit counters
						$hits=WT_DB::prepare(
							"SELECT page_name, SUM(page_count)".
							" FROM `##hit_counter`".
							" WHERE gedcom_id=? AND page_parameter IN (?, ?)".
							" GROUP BY page_name"
						)->execute(array(WT_GED_ID, $gid1, $gid2))->fetchAssoc();
						foreach ($hits as $page_name=>$page_count) {
							WT_DB::prepare(
								"UPDATE `##hit_counter` SET page_count=?".
								" WHERE gedcom_id=? AND page_name=? AND page_parameter=?"
							)->execute(array($page_count, WT_GED_ID, $page_name, $gid1));
						}
						WT_DB::prepare(
							"DELETE FROM `##hit_counter`".
							" WHERE gedcom_id=? AND page_parameter=?"
						)->execute(array(WT_GED_ID, $gid2));
					}
					$newgedrec = "0 @$gid1@ $type1\n";
					for ($i=0; ($i<count($facts1) || $i<count($facts2)); $i++) {
						if (isset($facts1[$i])) {
							if (in_array($i, $keep1)) {
								$newgedrec .= $facts1[$i]['subrec']."\n";
								echo WT_I18N::translate('Adding'), " ", $facts1[$i]['fact'], ' ', WT_I18N::translate('from'), ' ', $gid1, '<br>';
							}
						}
						if (isset($facts2[$i])) {
							if (in_array($i, $keep2)) {
								$newgedrec .= $facts2[$i]['subrec']."\n";
								echo WT_I18N::translate('Adding'), ' ', $facts2[$i]['fact'], ' ', WT_I18N::translate('from'), ' ', $gid2, '<br>';
							}
						}
					}

					replace_gedrec($gid1, WT_GED_ID, $newgedrec);
					$rec=WT_GedcomRecord::getInstance($gid1);
					echo
						'<p>',
							WT_I18N::translate(
								'Record %s successfully updated.',
								'<a href="'.$rec->getHtmlUrl().'">'.$rec->getXref().'</a>'
							),
						'</p';
					$fav_count=update_favorites($gid2, $gid1);
					if ($fav_count > 0) {
						echo '<p>', $fav_count, ' ', WT_I18N::translate('favorites updated.'), '<p>';
					}
					echo '</div>';
				}
			}
		}
	}
}
if ($action=='choose') {
	$controller->addInlineJavascript('
		function iopen_find(textbox, gedselect) {
			ged = gedselect.options[gedselect.selectedIndex].value;
			findIndi(textbox, null, ged);
		}
		function fopen_find(textbox, gedselect) {
			ged = gedselect.options[gedselect.selectedIndex].value;
			findFamily(textbox, ged);
		}
		function sopen_find(textbox, gedselect) {
			ged = gedselect.options[gedselect.selectedIndex].value;
			findSource(textbox, null, ged);		}
	');

	echo 
		'<div id="merge"><h3>', WT_I18N::translate('Merge records'), '</h3>
		<form method="post" name="merge" action="admin_site_merge.php">
		<input type="hidden" name="action" value="select">
		<p>', WT_I18N::translate('Select two GEDCOM records to merge.  The records must be of the same type.'), '</p>
		<table><tr>
		<td>',
		WT_I18N::translate('Merge To ID:'),
		'</td><td>
		<input type="text" name="gid1" id="gid1" value="', $gid1, '" size="10" tabindex="1" autofocus="autofocus">
		<select name="ged" tabindex="4"';
	if (count(WT_Tree::getAll())==1) {
		echo 'style="width:1px;visibility:hidden;"';
	}
	echo ' >';
	foreach (WT_Tree::getAll() as $tree) {
		echo '<option value="', $tree->tree_name_html, '"';
		if (empty($ged) && $tree->tree_id==WT_GED_ID || !empty($ged) && $ged==$tree->tree_name) {
			echo ' selected="selected"';
		}
		echo ' dir="auto">', $tree->tree_title_html, '</option>';
	}
	echo
		'</select>
		<a href="#" onclick="iopen_find(document.merge.gid1, document.merge.ged);" tabindex="6" class="icon-button_indi" title="'.WT_I18N::translate('Find an individual').'"></a>
		<a href="#" onclick="fopen_find(document.merge.gid1, document.merge.ged);" tabindex="8" class="icon-button_family" title="'.WT_I18N::translate('Find a family').'"></a>
		<a href="#" onclick="sopen_find(document.merge.gid1, document.merge.ged);" tabindex="10" class="icon-button_source" title="'.WT_I18N::translate('Find a source').'"></a>
		</td></tr><tr><td>',
		WT_I18N::translate('Merge From ID:'),
		'</td><td>
		<input type="text" name="gid2" id="gid2" value="', $gid2, '" size="10" tabindex="2">&nbsp;',
		'<select name="ged2" tabindex="5"';
	if (count(WT_Tree::getAll())==1) {
		echo 'style="width:1px;visibility:hidden;"';
	}
	echo ' >';
	foreach (WT_Tree::getAll() as $tree) {
		echo '<option value="', $tree->tree_name_html, '"';
		if (empty($ged2) && $tree->tree_id==WT_GED_ID || !empty($ged2) && $ged2==$tree->tree_name) {
			echo ' selected="selected"';
		}
		echo ' dir="auto">', $tree->tree_title_html, '</option>';
	}
	echo
		'</select>
		<a href="#" onclick="iopen_find(document.merge.gid2, document.merge.ged2);" tabindex="7" class="icon-button_indi" title="'.WT_I18N::translate('Find an individual').'"></a>
		<a href="#" onclick="fopen_find(document.merge.gid2, document.merge.ged2);" tabindex="9" class="icon-button_family" title="'.WT_I18N::translate('Find a family').'"></a>
		<a href="#" onclick="sopen_find(document.merge.gid2, document.merge.ged2);" tabindex="11" class="icon-button_source" title="'.WT_I18N::translate('Find a source').'"></a>
		</td></tr></table>
		<input type="submit" value="', WT_I18N::translate('next'), '" tabindex="3">
		</form></div>';
}
