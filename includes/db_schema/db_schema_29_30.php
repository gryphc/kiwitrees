<?php
// Update the database schema from version 29 to 30
// - add widgets to module system
//
// The script should assume that it can be interrupted at
// any point, and be able to continue by re-running the script.
// Fatal errors, however, should be allowed to throw exceptions,
// which will be caught by the framework.
// It shouldn't do anything that might take more than a few
// seconds, for systems with low timeout values.
//
// Derived from webtrees
// Copyright (C) 2014 Greg Roach
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

// add resource to module_privacy components
self::exec("ALTER TABLE `##module_privacy` CHANGE component component ENUM('block', 'chart', 'menu', 'report', 'sidebar', 'tab', 'theme', 'widget', 'resource')");

// Delete old menu settings
self::exec("DELETE FROM `##module_privacy` WHERE `module_name` = 'no_census' and `component` = 'menu'");
self::exec("UPDATE `##module` SET `menu_order` = null WHERE `module_name` = 'no_census'");

// Update the version to indicate success
WT_Site::preference($schema_name, $next_version);