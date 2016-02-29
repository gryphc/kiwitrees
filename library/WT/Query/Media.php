<?php
// Static functions to support media lists
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class WT_Query_Media {
	// Generate a list of all the folders in the current tree - for the media list.
	public static function folderList() {
		$folders = WT_DB::prepare(
			"SELECT SQL_CACHE LEFT(m_filename, CHAR_LENGTH(m_filename) - CHAR_LENGTH(SUBSTRING_INDEX(m_filename, '/', -1))) AS media_path" .
			" FROM  `##media`" .
			" WHERE m_file = ?" .
			"	AND   m_filename NOT LIKE 'http://%'" .
			" AND   m_filename NOT LIKE 'https://%'" .
			" GROUP BY 1" .
			" ORDER BY 1"
		)->execute(array(WT_GED_ID))->fetchOneColumn();

		if (!$folders || reset($folders)!='') {
			array_unshift($folders, '');
		}

		return array_combine($folders, $folders);
	}

	// Generate a list of all folders from all the trees - for the media admin.
	public static function folderListAll() {
		$folders = WT_DB::prepare(
			"SELECT SQL_CACHE LEFT(m_filename, CHAR_LENGTH(m_filename) - CHAR_LENGTH(SUBSTRING_INDEX(m_filename, '/', -1))) AS media_path" .
			" FROM  `##media`" .
			" WHERE m_filename NOT LIKE 'http://%'" .
			" AND   m_filename NOT LIKE 'https://%'" .
			" GROUP BY 1" .
			" ORDER BY 1"
		)->execute()->fetchOneColumn();

		if ($folders) {
			return array_combine($folders, $folders);
		} else {
			return array();
		}
	}

	// Generate a filtered, sourced, privacy-checked list of media objects - for the media list.
	public static function mediaList($folder, $subfolders, $sort, $filter, $form_type) {
		// All files in the folder, plus external files
		$sql = 
			"SELECT 'OBJE' AS type, m_id AS xref, m_file AS ged_id, m_gedcom AS gedrec, m_titl, m_filename" .
			" FROM `##media`" .
			" WHERE m_file=?";
		$args = array(
			WT_GED_ID,
		);

		// Only show external files when we are looking at the root folder
		if ($folder=='') {
			$sql_external = " OR m_filename LIKE 'http://%' OR m_filename LIKE 'https://%'";
		} else {
			$sql_external = "";
		}

		// Include / exclude subfolders (but always include external)
		switch ($subfolders) {
		case 'include':
			$sql .= " AND (m_filename LIKE CONCAT(?, '%') $sql_external)";
			$args[] = $folder;
			break;
		case 'exclude':
			$sql .= " AND (m_filename LIKE CONCAT(?, '%')  AND m_filename NOT LIKE CONCAT(?, '%/%') $sql_external)";
			$args[] = $folder;
			$args[] = $folder;
			break;
		default:
			throw new Exception('Bad argument (subfolders=', $subfolders, ') in WT_Query_Media::mediaList()');
		}

		// Apply search terms
		if ($filter) {
			$sql .= " AND (m_filename LIKE CONCAT('%', ?, '%') OR m_titl LIKE CONCAT('%', ?, '%'))";
			$args[] = $filter;
			$args[] = $filter;
		}

		if ($form_type) {
			$sql .= " AND (m_gedcom LIKE CONCAT('%\n3 TYPE ', ?, '%'))";
			$args[] = $form_type;
		}

		switch ($sort) {
		case 'file':
			$sql .= " ORDER BY m_filename";
			break;
		case 'title':
			$sql .= " ORDER BY m_titl";
			break;
		default:
			throw new Exception('Bad argument (sort=', $sort, ') in WT_Query_Media::mediaList()');
		}

		$rows = WT_DB::prepare($sql)->execute($args)->fetchAll(PDO::FETCH_ASSOC);
		$list = array();
		foreach ($rows as $row) {
			$media = WT_Media::getInstance($row);
			if ($media->canDisplayDetails()) {
				$list[] = $media;
			}
		}
		return $list;
	}
}
