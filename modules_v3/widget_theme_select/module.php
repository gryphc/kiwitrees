<?php
// Classes and libraries for module system
//
// webtrees: Web based Family History software
// Copyright (C) 2014 webtrees development team.
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
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class widget_theme_select_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Theme change');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Theme change” module */ WT_I18N::translate('An alternative way to select a new theme.');
	}

	// Implement class WT_Module_Block
	public function getWidget($widget_id, $template = true, $cfg = null) {
		$id = $this->getName().$widget_id;
		$class = $this->getName();
		$title = $this->getTitle();
		$menu = WT_MenuBar::getThemeMenu();

		if ($menu) {
			$content = '<div class="center theme_form">'.WT_MenuBar::getThemeMenu().'</div><br>';
		}

		if ($template) {
			require WT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement WT_Module_Widget
	public function defaultWidgetOrder() {
		return 130;
	}

	// Implement class WT_Module_Block
	public function configureBlock($block_id) {
	}
}