<?php

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class online_begraafplaatsen_plugin extends research_base_plugin {
	static function getName() {
		return 'Online Begraafplaatsen';
	}	
	
	static function create_link($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		// querystrings are not possible anymore due to changes in website functionality. Just present the link to the website.
		return $link = 'http://www.online-begraafplaatsen.nl/zoeken.asp';
	}

	static function create_sublink($fullname, $givn, $first, $middle, $prefix, $surn, $surname) {
		return false;
	}
	
	static function encode_plus() {
		return false;	
	}
}