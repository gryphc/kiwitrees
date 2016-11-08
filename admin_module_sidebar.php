<?php
// Module Administration User Interface.
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

define('WT_SCRIPT_NAME', 'admin_module_sidebar.php');
require 'includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Page();
$controller
	->restrictAccess(WT_USER_IS_ADMIN)
	->setPageTitle(WT_I18N::translate('Module administration'))
	->pageHeader()
	->addInlineJavascript('
    jQuery("#sidebars_table").sortable({items: ".sortme", forceHelperSize: true, forcePlaceholderSize: true, opacity: 0.7, cursor: "move", axis: "y"});

    //-- update the order numbers after drag-n-drop sorting is complete
    jQuery("#sidebars_table").bind("sortupdate", function(event, ui) {
			jQuery("#"+jQuery(this).attr("id")+" input").each(
				function (index, value) {
					value.value = index+1;
				}
			);
		});
	');

$modules=WT_Module::getActiveSidebars(WT_GED_ID, WT_PRIV_HIDE);

$action = safe_POST('action');

if ($action=='update_mods' && WT_Filter::checkCsrf()) {
	foreach ($modules as $module_name=>$module) {
		foreach (WT_Tree::getAll() as $tree) {
			$access_level = safe_POST("access-{$module_name}-{$tree->tree_id}", WT_REGEX_INTEGER, $module->defaultAccessLevel());
			WT_DB::prepare(
				"REPLACE INTO `##module_privacy` (module_name, gedcom_id, component, access_level) VALUES (?, ?, 'sidebar', ?)"
			)->execute(array($module_name, $tree->tree_id, $access_level));
		}
		$order = safe_POST('order-'.$module_name);
		WT_DB::prepare(
			"UPDATE `##module` SET sidebar_order=? WHERE module_name=?"
		)->execute(array($order, $module_name));
		$module->order=$order; // Make the new order take effect immediately
	}
	uasort($modules, create_function('$x,$y', 'return $x->order > $y->order;'));
}

?>
<div id="sidebars">
	<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="action" value="update_mods">
		<?php echo WT_Filter::getCsrf(); ?>
		<table id="sidebars_table" class="modules_table">
			<thead>
				<tr>
					<th><?php echo WT_I18N::translate('Sidebar'); ?></th>
					<th><?php echo WT_I18N::translate('Description'); ?></th>
					<th><?php echo WT_I18N::translate('Order'); ?></th>
					<th><?php echo WT_I18N::translate('Access level'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				$order = 1;
				foreach ($modules as $module) {
					?>
					<tr class="sortme">
						<td>
							<?php
							if ( $module instanceof WT_Module_Config ) {
								echo '<a href="', $module->getConfigLink(), '">';
							}
							echo $module->getTitle();
							if ( $module instanceof WT_Module_Config && array_key_exists($module->getName(), WT_Module::getActiveModules() ) ) {
								echo ' <i class="fa fa-cogs"></i></a>';
							}
							?>
						</td>
						<td>
							<?php echo $module->getDescription(); ?>
						</td>
						<td>
							<input type="text" size="3" value="<?php echo $order; ?>" name="order-<?php echo $module->getName(); ?>">
						</td>
						<td>
							<table class="modules_table2">
								<?php foreach (WT_Tree::getAll() as $tree) { ?>
									<tr>
										<td>
											<?php echo $tree->tree_title_html; ?>
										</td>
										<td>
											<?php
												$access_level = WT_DB::prepare(
													"SELECT access_level FROM `##module_privacy` WHERE gedcom_id=? AND module_name=? AND component='sidebar'"
												)->execute(array($tree->tree_id, $module->getName()))->fetchOne();
												if ($access_level === null) {
													$access_level = $module->defaultAccessLevel();
												}
												echo edit_field_access_level('access-' . $module->getName() . '-' . $tree->tree_id, $access_level);
											?>
										</td>
									</tr>
								<?php } ?>
							</table>
						</td>
					</tr>
				<?php
				$order++;
				}
				?>
			</tbody>
		</table>
		<button class="btn btn-primary show" type="submit">
			<i class="fa fa-floppy-o"></i>
			<?php echo WT_I18N::translate('save'); ?>
		</button>
	</form>
</div>
