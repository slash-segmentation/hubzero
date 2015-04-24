<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2011 Purdue University. All rights reserved.
 *
 * This file is part of: The HUBzero(R) Platform for Scientific Collaboration
 *
 * The HUBzero(R) Platform for Scientific Collaboration (HUBzero) is free
 * software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option) any
 * later version.
 *
 * HUBzero is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * HUBzero is a registered trademark of Purdue University.
 *
 * @package   hubzero-cms
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

$canDo = WorkflowserviceHelper::getActions('category');

$text = ($this->task == 'edit' ? JText::_('JACTION_EDIT') : JText::_('JACTION_CREATE'));

JHTML::_('behavior.modal');

JToolBarHelper::title(JText::_('COM_WORKFLOWSERVICE_TITLE') . ': ' . JText::_('COM_WORKFLOWSERVICE_QUESTIONS') . ': ' . $text, 'answers.png');
if ($canDo->get('core.edit'))
{
	JToolBarHelper::apply();
	JToolBarHelper::save();
	JToolBarHelper::spacer();
}
JToolBarHelper::cancel();
JToolBarHelper::spacer();
JToolBarHelper::help('category');
?>
<script type="text/javascript">
function submitbutton(pressbutton)
{
	var form = document.adminForm;

	if (pressbutton == 'cancel') {
		submitform( pressbutton );
		return;
	}

	// do field validation
	if (document.getElementById('field-category').value == ''){
		alert('<?php echo JText::_('COM_WORKFLOWSERVICE_ERROR_MISSING_CATEGORY'); ?>');
	} else {
		<?php echo JFactory::getEditor()->save('text'); ?>

		submitform(pressbutton);
	}
}
</script>

<form action="index.php" method="post" name="adminForm" id="item-form">
	<div class="col width-60 fltlft">
		<fieldset class="adminform">
			<legend><span><?php echo JText::_('JDETAILS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-category"><?php echo JText::_('COM_WORKFLOWSERVICE_FIELD_CATEGORY'); ?>: <span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />
				<input type="text" name="category[category]" id="field-category" size="30" maxlength="250" value="<?php echo $this->escape($this->row->category('raw')); ?>" />
			</div>
			<div class="input-wrap">
				<label for="field-category"><?php echo JText::_('COM_WORKFLOWSERVICE_FIELD_ORDERING'); ?>: <span class="required"><?php echo JText::_('JOPTION_REQUIRED'); ?></span></label><br />
				<input type="text" name="category[ordering]" id="field-category" size="5" maxlength="2" value="<?php echo $this->row->get('ordering'); ?>" />
			</div>
			<div class="input-wrap">
				<label for="field-category"><?php echo JText::_('COM_WORKFLOWSERVICE_FIELD_WORKFLOWS'); ?>:</label> <A href="index.php?option=com_workflowservice&view=workflowassistant&task=workflowassistant&tmpl=component&id=<?php echo $this->row->get('id'); ?>" class="modal"  rel="{size: {x: 100, y: 500}, handler:'iframe'}"> ** Help me select workflows **</a>
				<textarea id="category_workflows" name="category[workflows]" rows="7"><?php echo $this->row->get('workflows'); ?></textarea>
			</div>
		</fieldset>
	</div>
	<div class="col width-40 fltrt">
		<table class="meta">
			<tbody>
				<tr>
					<th><?php echo JText::_('COM_WORKFLOWSERVICE_FIELD_ID'); ?>:</th>
					<td>
						<?php echo $this->row->get('id', 0); ?>
						<input type="hidden" name="category[id]" value="<?php echo $this->row->get('id'); ?>" />
					</td>
				</tr>
			<?php if ($this->row->get('id')) { ?>
				<tr>
					<th><?php echo JText::_('COM_WORKFLOWSERVICE_FIELD_CREATED'); ?>:</th>
					<td><?php echo $this->row->get('created'); ?></td>
				</tr>
			<?php } ?>
			</tbody>
		</table>

		<fieldset class="adminform">
			<legend><span><?php echo JText::_('COM_WORKFLOWSERVICE_PARAMETERS'); ?></span></legend>

			<div class="input-wrap">
				<label for="field-state"><?php echo JText::_('COM_WORKFLOWSERVICE_FIELD_STATE'); ?>:</label><br />
				<select name="category[state]" id="field-state">
					<option value="0"<?php echo ($this->row->get('state') == 0) ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_WORKFLOWSERVICE_STATE_OPEN'); ?></option>
					<option value="1"<?php echo ($this->row->get('state') == 1) ? ' selected="selected"' : ''; ?>><?php echo JText::_('COM_WORKFLOWSERVICE_STATE_CLOSED'); ?></option>
				</select>
			</div>
		</fieldset>
	</div>
	<div class="clr"></div>
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="save" />

	<?php echo JHTML::_('form.token'); ?>
</form>
