<?php

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class bhic_nl_plugin extends research_base_plugin {
	static function getName() {
		return 'Brabants Historisch Info.';
	}

	static function getPaySymbol() {
		return false;
	}

	static function getSearchArea() {
		return 'NLD';
	}

	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return $link = 'https://www.bhic.nl/memorix/genealogy/search?serviceUrl=%2Fgenealogie%2F%3Fq_text%3D' . $givn . '%2B' . $surn;
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}

	static function encode_plus() {
		return false;
	}
}