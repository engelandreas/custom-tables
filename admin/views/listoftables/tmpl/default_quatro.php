<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Access\Access;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\String\PunycodeHelper;

use CustomTables\IntegrityChecks;

HTMLHelper::_('behavior.multiselect');

$loggeduser = Factory::getUser();

?>
<form action="<?php echo Route::_('index.php?option=com_customtables&view=listoftables'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php
				// Search tools bar
				echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
				?>
				<?php if (empty($this->items)) : ?>
					<div class="alert alert-info">
						<span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
						<?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
					</div>
				<?php else : ?>
				
					<?php
						$result = IntegrityChecks::check();
	
						if(count($result)>0)
							Factory::getApplication()->enqueueMessage('<ol><li>'.implode('</li><li>',$result).'</li></ol>','notice');
					?>
				
					<table class="table" id="userList">
						<caption class="visually-hidden">
							<?php echo Text::_('COM_USERS_USERS_TABLE_CAPTION'); ?>,
							<span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
							<span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
						</caption>
						<thead>
							<?php include('default_quatro_head.php');?>
						</thead>
						<tbody>
							<?php echo $this->loadTemplate('quatro_body');?>
						
						<?php /*
						<?php foreach ($this->items as $i => $item) :
							$canEdit   = $this->canDo->get('core.edit');
							$canChange = $loggeduser->authorise('core.edit.state',	'com_users');

							// If this group is super admin and this user is not super admin, $canEdit is false
							if ((!$loggeduser->authorise('core.admin')) && Access::check($item->id, 'core.admin'))
							{
								$canEdit   = false;
								$canChange = false;
							}
						?>
						
						
							<tr class="row<?php echo $i % 2; ?>">
								<td class="text-center">
									<?php if ($canEdit || $canChange) : ?>
										<?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
									<?php endif; ?>
								</td>
								<th scope="row">
									<div class="name break-word">
									<?php if ($canEdit) : ?>
										<a href="<?php echo Route::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->id); ?>" title="<?php echo Text::sprintf('COM_USERS_EDIT_USER', $this->escape($item->name)); ?>">
											<?php echo $this->escape($item->name); ?></a>
									<?php else : ?>
										<?php echo $this->escape($item->name); ?>
									<?php endif; ?>
									</div>
									<div class="btn-group">
										<?php echo HTMLHelper::_('users.addNote', $item->id); ?>
										<?php if ($item->note_count > 0) : ?>
										<button type="button" class="btn btn-secondary btn-sm dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<span class="visually-hidden"><?php echo Text::_('JGLOBAL_TOGGLE_DROPDOWN'); ?></span>
										</button>
										<div class="dropdown-menu">
											<?php echo HTMLHelper::_('users.filterNotes', $item->note_count, $item->id); ?>
											<?php echo HTMLHelper::_('users.notes', $item->note_count, $item->id); ?>
										</div>
										<?php endif; ?>
									</div>
									<?php echo HTMLHelper::_('users.notesModal', $item->note_count, $item->id); ?>
									<?php if ($item->requireReset == '1') : ?>
										<span class="badge bg-warning text-dark"><?php echo Text::_('COM_USERS_PASSWORD_RESET_REQUIRED'); ?></span>
									<?php endif; ?>
								</th>
								<td class="text-center btns d-none d-md-table-cell">
									<a href="<?php echo Route::_('index.php?option=com_users&view=debuguser&user_id=' . (int) $item->id); ?>">
										<span class="icon-list" aria-hidden="true"></span>
										<span class="visually-hidden"><?php echo Text::_('COM_USERS_DEBUG_PERMISSIONS'); ?></span>
									</a>
								</td>
								<td class="break-word d-none d-md-table-cell">
									<?php echo $this->escape($item->username); ?>
								</td>
								<td class="text-center d-md-table-cell">
									<?php $self = $loggeduser->id == $item->id; ?>
									<?php if ($canChange) : ?>
										<?php echo HTMLHelper::_('jgrid.state', HTMLHelper::_('users.blockStates', $self), $item->block, $i, 'users.', !$self); ?>
									<?php else : ?>
										<?php echo HTMLHelper::_('jgrid.state', HTMLHelper::_('users.blockStates', $self), $item->block, $i, 'users.', false); ?>
									<?php endif; ?>
								</td>
								<td class="text-center d-md-table-cell">
									<?php
									$activated = empty( $item->activation) ? 0 : 1;
									echo HTMLHelper::_('jgrid.state', HTMLHelper::_('users.activateStates'), $activated, $i, 'users.', (boolean) $activated);
									?>
								</td>
								<?php if ($tfa) : ?>
								<td class="text-center d-none d-md-table-cell">
									<span class="tbody-icon">
									<?php if (!empty($item->otpKey)) : ?>
										<span class="icon-check" aria-hidden="true"></span>
										<span class="visually-hidden"><?php echo Text::_('COM_USERS_TFA_ACTIVE'); ?></span>
									<?php else : ?>
										<span class="icon-times" aria-hidden="true"></span>
										<span class="visually-hidden"><?php echo Text::_('COM_USERS_TFA_NOTACTIVE'); ?></span>
									<?php endif; ?>
									</span>
								</td>
								<?php endif; ?>
								<td class="d-none d-md-table-cell">
									<?php if (substr_count($item->group_names, "\n") > 1) : ?>
										<span tabindex="0"><?php echo Text::_('COM_USERS_USERS_MULTIPLE_GROUPS'); ?></span>
										<div role="tooltip" id="tip<?php echo $i; ?>">
											<strong><?php echo Text::_('COM_USERS_HEADING_GROUPS'); ?></strong>
											<ul><li><?php echo str_replace("\n", '</li><li>', $item->group_names); ?></li></ul>
										</div>
									<?php else : ?>
										<?php echo nl2br($item->group_names, false); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-xl-table-cell break-word">
									<?php echo PunycodeHelper::emailToUTF8($this->escape($item->email)); ?>
								</td>
								<td class="d-none d-xl-table-cell">
									<?php if ($item->lastvisitDate !== null) : ?>
										<?php echo HTMLHelper::_('date', $item->lastvisitDate, Text::_('DATE_FORMAT_LC6')); ?>
									<?php else : ?>
										<?php echo Text::_('JNEVER'); ?>
									<?php endif; ?>
								</td>
								<td class="d-none d-xl-table-cell">
									<?php echo HTMLHelper::_('date', $item->registerDate, Text::_('DATE_FORMAT_LC6')); ?>
								</td>
								<td class="d-none d-md-table-cell">
									<?php echo (int) $item->id; ?>
								</td>
							</tr>
							<?php endforeach; ?>
						<? */ ?>
						</tbody>
					</table>

					<?php // load the pagination. ?>
					<?php echo $this->pagination->getListFooter(); ?>

					<?php // Load the batch processing form if user is allowed ?>
					<?php if ($loggeduser->authorise('core.create', 'com_customtables','categories')
						&& $loggeduser->authorise('core.edit', 'com_customtables','categories')
						&& $loggeduser->authorise('core.edit.state', 'com_customtables','categories')) : ?>
						<?php echo HTMLHelper::_(
							'bootstrap.renderModal',
							'collapseModal',
							array(
								'title'  => Text::_('COM_CUSTOMTABLES_BATCH_OPTIONS'),
								'footer' => $this->loadTemplate('batch_footer'),
							),
							$this->loadTemplate('batch_body')
						); ?>
					<?php endif; ?>
				<?php endif; ?>

				<input type="hidden" name="task" value="">
				<input type="hidden" name="boxchecked" value="0">
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>
