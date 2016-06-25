<?php
/*
 * Fancy Tree View Module - Help text
 *
 * webtrees: Web based Family History software
 * Copyright (C) 2014 webtrees development team.
 * Copyright (C) 2014 JustCarmen.
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
 */

if (!defined('WT_WEBTREES') || !defined('WT_SCRIPT_NAME') || WT_SCRIPT_NAME!='help_text.php') {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

switch ($help) {

case 'add_surname':
	$title=WT_I18N::translate('Add a surname');
	$text=WT_I18N::translate(	'<p>Here you can enter a surname. The script will automatically search for the oldest person with this surname.</p>'.
								'<p>This person will act as the root person for this family branch. Potential you can perform a phonetic search by placing a check mark in one or both phonetic algorithms.</p>'.
								'<p>After the search is complete, it is possible to change the ID of the root person. You can enter as much surnames as you like. There will be a menu-link and page created for each surname.</p>'.
								'<p>By dragging and dropping the table entries which will appear after you have set the first surname, you can sort the pages in any way you want.</p>');
	break;
case 'edit_surname':
	$title=WT_I18N::translate('Edit the surname (for display in the menu)');
	$text=WT_I18N::translate(	'<p>The displayed surname is the surname used in the menu. It is possible to change the displayed surname to a more appropriate name.</p>'.
								'<p>If you click on the surname, an “edit”-field appears where you can change the surname. Click on the “save” button to save your input.</p>'.
								'<p>Note: this option is not available when the option “%s” is checked.</p>', WT_I18N::translate('Use fullname in menu'));
	break;
case 'numblocks':
	$title=WT_I18N::translate('Number of generation blocks to show');
	$text=WT_I18N::translate(	'<p>This option is especially usefull for large trees. When you notice a slow page load, here you can set the number of generation blocks to load at once to a lower level.</p>'.
								'<p>Below the last generation block a button will appear to add the next set of generation blocks. The new blocks will be added to the blocks already loaded.</p>'.
								'<p>Clicking on a “follow” link in the last visible generation block, will also load the next set of generation blocks.</p>' );
	break;
case 'check_relationship':
	$title=WT_I18N::translate('Check relationship between partners');
	$text=WT_I18N::translate(	'<p>With this option turned on, the script checks if a (married) couple has the same ancestors.</p>'.
								'<p>If a relationship between the partners is found, a text will appear between brackets after the spouses’ name to indicate the relationship.</p>'.
								'<p>Note: this option can cause slower page loading, especially on large trees. If you notice such a behavior, reduce the number of generation blocks to load at once (see the previous option).</p>');
	break;
case 'show_singles':
	$title=WT_I18N::translate('Show single persons');
	$text=WT_I18N::translate(	'<p>Turn this option on if you want to show single persons in the generation blocks. Single persons are persons without partner and children.</p>'.
								'<p>With this option turned on, every child of a family will be shown in a detailed way in the next generation block.</p>');
	break;
case 'gedcom_places':
	$title=WT_I18N::translate('Place settings');
	$text=WT_I18N::translate(	'<p>If you have ticked the “Show places” option, you can choose to use the default Gedcom settings to abbreviate placenames. If you don’t set this option, full place names will be shown.</p>');
	break;
case 'select_country':
	$title=WT_I18N::translate('Select your country');
	$text=WT_I18N::translate(	'<p>If you have ticked the “Show places” option but NOT the option to abbreviate placenames, you can set your own country here. Full places will be listed on the Fancy Tree View pages, but when a place includes the name of your own country, this name will be left out.</p>'.
								'<p>If you don’t select a country then all countries will be shown, including your own.</p>');
	break;
case 'resize_thumbs':
	$title=WT_I18N::translate('Resize thumbnails');
	$text=WT_I18N::translate(	'<p>Here you can choose to resize the default thumbnails especially for the Fancy Tree View pages. You can set a custom size in percentage or in pixels.</p><dl><dt>Size in percentage</dt><dd>The original thumbnails will be proportionally resized. This may result in a different width and height for each thumbnail.</dd><dt>Size in pixels</dt><dd>The longest side of the image will be resized to match the size in pixels. The other side will be resized proportionally.</dd><dt>Square thumbs</dt><dd>When you use a square thumbnail, all thumbnails will have the same width and height. In this case the thumbnails will be cropped.</dd></dl><p>If you untick the checkbox the default thumbnails will be used with the formats you have set on the tree configuration page.</p>');
	break;
case 'show_pdf':
	$title=WT_I18N::translate('PDF not supported for RTL-languages');
	$text=WT_I18N::translate(	'<p>Currently the PDF option is only supported for LTR-languages. These are all languages in which the text is read from left to right. The PDF icon will be disabled when the user selects a RTL-language. In a RTL language the text is read from right to left.</p>');
	break;
}
