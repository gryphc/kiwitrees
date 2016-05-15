<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class voorouder_plugin extends research_base_plugin {
	static function getName() {
		return 'Voorouder.nl';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname, $birth_year) {
		return $link = 'http://www.voorouder.nl/genealogie/search.php?mybool=AND&nr=50&showdeath=yes&mylastname=' . $surname .'&lnqualify=equals&myfirstname=' . $givn .'&fnqualify=contains';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}
