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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tree_WT_Module extends WT_Module implements WT_Module_Tab {
	var $headers; // CSS and script to include in the top of <head> section, before theme’s CSS
	var $js; // the TreeViewHandler javascript

	// Extend WT_Module. This title should be normalized when this module will be added officially
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Interactive tree');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the “Interactive tree” module */ WT_I18N::translate('An interactive tree, showing all the ancestors and descendants of an individual.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 70;
	}

	// Implement WT_Module_Tab
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return false;
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;

		require_once WT_MODULES_DIR . $this->getName() . '/class_treeview.php';
		$tv = new TreeView('tvTab');
		list($html, $js) = $tv->drawViewport($controller->record, 3);
		return
			'<script src="' . $this->js() . '"></script>' .
			'<script src="' . WT_JQUERYUI_TOUCH_PUNCH . '"></script>' .
			'<script>' . $js . '</script>' .
			$html;
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		global $SEARCH_SPIDER;

		return !$SEARCH_SPIDER;
	}

	// Implement WT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		// We cannot use jQuery("head").append(<link rel="stylesheet" ...as jQuery is not loaded at this time
		return
			'<script>
			if (document.createStyleSheet) {
				document.createStyleSheet("' . $this->css() . '"); // For Internet Explorer
			} else {
				var newSheet=document.createElement("link");
    		newSheet.setAttribute("rel","stylesheet");
    		newSheet.setAttribute("type","text/css");
   			newSheet.setAttribute("href","' . $this->css() . '");
		    document.getElementsByTagName("head")[0].appendChild(newSheet);
			}
			</script>';
	}

	// Extend WT_Module
	// We define here actions to proceed when called, either by Ajax or not
	public function modAction($mod_action) {
		require_once WT_MODULES_DIR . $this->getName() . '/class_treeview.php';
		switch ($mod_action) {
		case 'treeview':
			global $controller;
			$controller = new WT_Controller_Chart();
			$tv = new TreeView('tv');
			ob_start();
			$person = $controller->getSignificantIndividual();
			list($html, $js) = $tv->drawViewport($person, 4);
			$html = '<div id="tree-page">' . $html . '</div>';

			$controller
				->setPageTitle(WT_I18N::translate('Interactive tree of %s', $person->getFullName()))
				->pageHeader()
				->addExternalJavascript($this->js())
				->addExternalJavascript(WT_JQUERYUI_TOUCH_PUNCH)
				->addInlineJavascript($js)
				->addInlineJavascript('
					if (document.createStyleSheet) {
						document.createStyleSheet("' . $this->css() . '"); // For Internet Explorer
					} else {
						jQuery("head").append(\'<link rel="stylesheet" type="text/css" href="' . $this->css() . '">\');
					}
				');

			echo $html;
			break;

		case 'getDetails':
			//$controller = new WT_Controller_Ajax();
			//$controller->pageHeader();
			Zend_Session::writeClose();
			header('Content-Type: text/html; charset=UTF-8');
			$pid = WT_Filter::get('pid', WT_REGEX_XREF);
			$i = WT_Filter::get('instance');
			$tv = new TreeView($i);
			$individual = WT_Person::getInstance($pid);
			if ($individual) {
				echo $tv->getDetails($individual);
			}
			break;

		case 'getPersons':
			//$controller = new WT_Controller_Ajax();
			//$controller->pageHeader();
			Zend_Session::writeClose();
			header('Content-Type: text/html; charset=UTF-8');
			$q = WT_Filter::get('q');
			$i = WT_Filter::get('instance');
			$tv = new TreeView($i);
			echo $tv->getPersons($q);
			break;

		default:
			header('HTTP/1.0 404 Not Found');
			break;
		}
	}

	public function css() {
		return WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/css/treeview.css';
	}

	public function js() {
		return WT_STATIC_URL . WT_MODULES_DIR . $this->getName() . '/js/treeview.js';
	}

}
