<?php
// GEDFact page
//
// GEDFact information about an individual
//
// kiwi-webtrees: Web based Family History software
// Copyright (C) 2014 kiwitrees.net
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2008  PGV Development Team.  All rights reserved.
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

$controller=new WT_Controller_Individual();

echo '<link href="'.WT_STATIC_URL.WT_MODULES_DIR.'GEDFact_assistant/css/gf_styles.css" rel="stylesheet" type="text/css" media="screen">';

global $tabno, $linkToID, $SEARCH_SPIDER;
global $SHOW_AGE_DIFF;
global $GEDCOM, $ABBREVIATE_CHART_LABELS;
global $show_full;
global $famid, $censyear, $censdate;

$summary=
	$controller->record->format_first_major_fact(WT_EVENTS_BIRT, 2).
	$controller->record->format_first_major_fact(WT_EVENTS_DEAT, 2);

$controller->medialink_assistant();
