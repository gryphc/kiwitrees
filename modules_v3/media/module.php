<?php
// Classes and libraries for module system
//
// kiwi-webtrees: Web based Family History software
// Copyright (C) 2014 kiwitrees.net
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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
// Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
//
// $Id$

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class media_WT_Module extends WT_Module implements WT_Module_Tab {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Media');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Media” module */ WT_I18N::translate('A tab showing the media objects linked to an individual.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 50;
	}

	protected $mediaCount = null;

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return WT_USER_CAN_EDIT || $this->get_media_count()>0;
	}
	
	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return $this->get_media_count()==0;
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;

		ob_start();
		echo '<table class="facts_table">';
		// Reorder media ------------------------------------
		if (WT_USER_GEDCOM_ADMIN && $this->get_media_count()>1) {
			echo '<tr><td colspan="2" class="descriptionbox rela">';
			echo '<span><a href="#" onclick="reorder_media(\''.$controller->record->getXref().'\'); return false;"><i class="icon-media-shuffle"></i>';
			echo WT_I18N::translate('Re-order media');
			echo '</a></span>';
			echo '</td></tr>';
		}
		$media_found = print_main_media($controller->record->getXref(), 0, true);
		if (!$media_found) {
			echo '<tr><td id="no_tab4" colspan="2" class="facts_value">', WT_I18N::translate('There are no media objects for this individual.'), '</td></tr>';
		}
		//-- New Media link
		if (WT_USER_CAN_EDIT && $controller->record->canDisplayDetails() && get_gedcom_setting(WT_GED_ID, 'MEDIA_UPLOAD') >= WT_USER_ACCESS_LEVEL) {
		?>
			<tr>
				<td class="facts_label"><?php echo WT_Gedcom_Tag::getLabel('OBJE'); ?></td>
				<td class="facts_value">
					<a href="#" onclick="window.open('addmedia.php?action=showmediaform&amp;linktoid=<?php echo $controller->record->getXref(); ?>&amp;ged=<?php echo WT_GEDURL; ?>', '_blank', edit_window_specs); return false;"> <?php echo WT_I18N::translate('Add a new media object'); ?></a>
					<?php echo help_link('OBJE'); ?>
					<br>
					<a href="#" onclick="window.open('inverselink.php?linktoid=<?php echo $controller->record->getXref(); ?>&amp;ged=<?php echo WT_GEDURL; ?>&amp;linkto=person', '_blank', find_window_specs); return false;"><?php echo WT_I18N::translate('Link to an existing media object'); ?></a>
				</td>
			</tr>
		<?php
		}
		?>
		</table>
		<?php
		return '<div id="'.$this->getName().'_content">'.ob_get_clean().'</div>';
	}

	/**
	* get the number of media items for this person
	* @return int
	*/
	function get_media_count() {
		global $controller;

		if ($this->mediaCount===null) {
			$this->mediaCount = 0;
			preg_match_all('/\d OBJE @(' . WT_REGEX_XREF . ')@/', $controller->record->getGedcomRecord(), $matches);
			foreach ($matches[1] as $match) {
				$obje = WT_Media::getInstance($match);
				if ($obje && $obje->canDisplayDetails()) {
					$this->mediaCount++;
				}
			}
			foreach ($controller->record->getSpouseFamilies() as $sfam) {
				preg_match_all('/\d OBJE @(' . WT_REGEX_XREF . ')@/', $sfam->getGedcomRecord(), $matches);
				foreach ($matches[1] as $match) {
					$obje = WT_Media::getInstance($match);
					if ($obje && $obje->canDisplayDetails()) {
						$this->mediaCount++;
					}
				}
			}
		}
		return $this->mediaCount;
	}

	// Implement WT_Module_Tab
	public function canLoadAjax() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER; // Search engines cannot use AJAX
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}
}
