<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2012 webtrees development team.
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

class lightbox_WT_Module extends WT_Module implements WT_Module_Tab {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Album');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Album” module */ WT_I18N::translate('An alternative to the “media” tab, and an enhanced image viewer.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 60;
	}

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
		global $controller, $sort_i;

		require_once WT_ROOT.WT_MODULES_DIR.'lightbox/functions/lightbox_print_media.php';
		$html='<div id="'.$this->getName().'_content">';
		//Show Lightbox-Album header Links
		if (WT_USER_CAN_EDIT) {
			$html.='<table class="facts_table"><tr>';
			$html.='<td class="descriptionbox rela">';
			// Add a new media object
			if (get_gedcom_setting(WT_GED_ID, 'MEDIA_UPLOAD') >= WT_USER_ACCESS_LEVEL) {
				$html.='<span><a href="#" onclick="window.open(\'addmedia.php?action=showmediaform&linktoid='.$controller->record->getXref().'\', \'_blank\', \'resizable=1,scrollbars=1,top=50,height=780,width=600\');return false;">';
				$html.='<img src="'.WT_STATIC_URL.WT_MODULES_DIR.'lightbox/images/image_add.png" id="head_icon" class="icon" title="'.WT_I18N::translate('Add a new media object').'" alt="'.WT_I18N::translate('Add a new media object').'">';
				$html.=WT_I18N::translate('Add a new media object');
				$html.='</a></span>';
				// Link to an existing item
				$html.='<span><a href="#" onclick="window.open(\'inverselink.php?linktoid='.$controller->record->getXref().'&linkto=person\', \'_blank\', \'resizable=1,scrollbars=1,top=50,height=300,width=450\');">';
				$html.= '<img src="'.WT_STATIC_URL.WT_MODULES_DIR.'lightbox/images/image_link.png" id="head_icon" class="icon" title="'.WT_I18N::translate('Link to an existing media object').'" alt="'.WT_I18N::translate('Link to an existing media object').'">';
				$html.=WT_I18N::translate('Link to an existing media object');
				$html.='</a></span>';
			}
			if (WT_USER_GEDCOM_ADMIN && $this->get_media_count()>1) {
				// Popup Reorder Media
				$html.='<span><a href="#" onclick="reorder_media(\''.$controller->record->getXref().'\')">';
				$html.='<img src="'.WT_STATIC_URL.WT_MODULES_DIR.'lightbox/images/images.png" id="head_icon" class="icon" title="'.WT_I18N::translate('Re-order media').'" alt="'.WT_I18N::translate('Re-order media').'">';
				$html.=WT_I18N::translate('Re-order media');
				$html.='</a></span>';
				$html.='</td>';
			}
			$html.='</tr></table>';
		}
		$media_found = false;

		// Used when sorting media on album tab page
		$html.='<table width="100%" cellpadding="0" border="0"><tr>';
		$html.='<td width="100%" valign="top" >';
		ob_start();
		lightbox_print_media($controller->record->getXref(), 0, true, 1); // map, painting, photo, tombstone)
		lightbox_print_media($controller->record->getXref(), 0, true, 2); // card, certificate, document, newspaper
		lightbox_print_media($controller->record->getXref(), 0, true, 3); // electronic, fiche, film
		lightbox_print_media($controller->record->getXref(), 0, true, 4); // book, magazine, manuscript
		lightbox_print_media($controller->record->getXref(), 0, true, 5); // audio, video
		lightbox_print_media($controller->record->getXref(), 0, true, 6); // coat of arms, other
		lightbox_print_media($controller->record->getXref(), 0, true, 7); // not in DB
		return
			$html.
			ob_get_clean().
			'</td></tr></table></div>';
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

	protected $mediaCount = null;

	private function get_media_count() {
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

	private function getJS() {
		return '';
	}
}
