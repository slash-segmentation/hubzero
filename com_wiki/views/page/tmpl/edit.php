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
 * @author    Shawn Rice <zooley@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

if (!$this->sub)
{
	$this->css();
}
$this->js('wiki.js', 'com_wiki')
     ->js('jquery.fileuploader.js', 'system');

$juser = JFactory::getUser();

JPluginHelper::importPlugin('hubzero');
$dispatcher = JDispatcher::getInstance();

$tags = $this->page->tags('string');

if ($this->page->exists())
{
	$lid = $this->page->get('id');
}
else
{
	$lid = JRequest::getInt('lid', (time() . rand(0,10000)), 'post');
}
?>
<header id="<?php echo ($this->sub) ? 'sub-content-header' : 'content-header'; ?>">
	<h2><?php echo $this->escape($this->title); ?></h2>
	<?php
	//if ($this->page->exists())
	//{
		$this->view('authors')
		     ->setBasePath($this->base_path)
		     ->set('page', $this->page)
		     ->display();
	//}
	?>
</header><!-- /#content-header -->

<?php
	$this->view('submenu')
	     ->setBasePath($this->base_path)
	     ->set('option', $this->option)
	     ->set('controller', $this->controller)
	     ->set('page', $this->page)
	     ->set('task', $this->task)
	     ->set('sub', $this->sub)
	     ->display();
?>

<section class="main section">
<?php
if ($this->page->exists() && !$this->page->access('modify')) {
	if ($this->page->param('allow_changes') == 1) { ?>
		<p class="warning"><?php echo JText::_('COM_WIKI_WARNING_NOT_AUTH_EDITOR_SUGGESTED'); ?></p>
<?php } else { ?>
		<p class="warning"><?php echo JText::_('COM_WIKI_WARNING_NOT_AUTH_EDITOR'); ?></p>
<?php }
}
?>

<?php if ($this->page->isLocked() && !$this->page->access('manage')) { ?>
	<p class="warning"><?php echo JText::_('COM_WIKI_WARNING_NOT_AUTH_EDITOR'); ?></p>
<?php } ?>

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>

<?php if ($this->preview) { ?>
	<div id="preview">
		<section class="main section">
			<p class="warning"><?php echo JText::_('COM_WIKI_WARNING_PREVIEW_ONLY'); ?></p>

			<div class="wikipage">
				<?php echo $this->revision->get('pagehtml'); ?>
			</div>
		</section><!-- / .section -->
	</div>
<?php } ?>

<form action="<?php echo JRoute::_($this->page->link()); ?>" method="post" id="hubForm"<?php echo ($this->sub) ? ' class="full"' : ''; ?>>
<?php if (!$this->sub) { ?>
	<div class="explaination">
	<?php if ($this->page->exists() && $this->page->access('edit')) { ?>
		<p><?php echo JText::sprintf('COM_WIKI_WARNING_TO_CHANGE_PAGENAME', JRoute::_($this->page->link('rename'))); ?></p>
	<?php } ?>
		<p><?php echo JText::sprintf('COM_WIKI_IMAGE_MACRO_HINT', JRoute::_('index.php?option=' . $this->option . '&scope=' . $this->page->get('scope') . '&pagename=Help:WikiMacros#image')); ?></p>
		<p><?php echo JText::sprintf('COM_WIKI_FILE_MACRO_HINT', JRoute::_('index.php?option=' . $this->option . '&scope=' . $this->page->get('scope') . '&pagename=Help:WikiMacros#file')); ?></p>

		<div id="file-manager" data-instructions="<?php echo JText::_('COM_WIKI_CLICK_OR_DROP_FILE'); ?>" data-action="<?php echo rtrim(JURI::getInstance()->base(true), '/'); ?>/index.php?option=com_wiki&amp;no_html=1&amp;controller=media&amp;task=upload&amp;listdir=<?php echo $lid; ?>" data-list="<?php echo rtrim(JURI::getInstance()->base(true), '/'); ?>/index.php?option=com_wiki&amp;no_html=1&amp;controller=media&amp;task=list&amp;listdir=<?php echo $lid; ?>">
			<iframe name="filer" id="filer" src="<?php echo rtrim(JURI::getInstance()->base(true), '/'); ?>/index.php?option=com_wiki&amp;tmpl=component&amp;controller=media&amp;scope=<?php echo $this->page->get('scope'); ?>&amp;pagename=<?php echo $this->page->get('pagename'); ?>&amp;listdir=<?php echo $lid; ?>"></iframe>
		</div>
		<div id="file-uploader-list"></div>
	</div>
<?php } else { ?>
	<?php if ($this->page->exists() && $this->page->access('edit')) { ?>
		<p><?php echo JText::sprintf('COM_WIKI_WARNING_TO_CHANGE_PAGENAME', JRoute::_($this->page->link('rename'))); ?></p>
	<?php } ?>
<?php } ?>
	<fieldset>
		<legend><?php echo JText::_('COM_WIKI_FIELDSET_PAGE'); ?></legend>

		<div class="grid">
			<div class="col span6">
				<label for="parent">
					<?php echo JText::_('COM_WIKI_FIELD_PARENT'); ?>:
					<select name="scope" id="parent">
						<option value=""><?php echo JText::_('COM_WIKI_NONE'); ?></option>
					<?php
						if ($this->tree)
						{
							foreach ($this->tree as $item)
							{
								if ($this->page->get('pagename') == $item->get('pagename'))
								{
									continue;
								}
					?>
						<option value="<?php echo $this->escape(stripslashes($item->get('scope'))); ?>"<?php if ($this->page->get('scope') == $item->get('scope')) { echo ' selected="selected"'; } ?>><?php echo $this->escape(stripslashes($item->get('scopeName'))); ?></option>
					<?php
							}
						}
					?>
					</select>
				</label>
			</div>
			<div class="col span6 omega">
				<label for="templates">
					<?php echo JText::_('COM_WIKI_FIELD_TEMPLATE'); ?>:
					<select name="tplate" id="templates">
						<option value="tc"><?php echo JText::_('COM_WIKI_FIELD_TEMPLATE_SELECT'); ?></option>
						<?php
							$hi = array();

							foreach ($this->book->templates('list', array(), true) as $template)
							{
								$tmpltags = $template->tags('string');
								if (strtolower($this->tplate) == strtolower($template->get('pagename')))
								{
									$tags = $tmpltags;
								}

								echo "\t" . '<option value="t' . $template->get('id') . '"';
								if (strtolower($this->tplate) == strtolower($template->get('pagename'))
								 || strtolower($this->tplate) == 't' . $template->get('id'))
								{
									echo ' selected="selected"';
									if (!$this->page->exists())
									{
										$this->revision->set('pagetext', stripslashes($template->revision()->get('pagetext')));
									}
								}
								echo '>' . $this->escape(stripslashes($template->get('title'))) . '</option>' . "\n";

								$j  = '<input type="hidden" name="t' . $template->get('id') . '" id="t' . $template->get('id') . '" value="' . $this->escape(stripslashes($template->revision()->get('pagetext'))) . '" />' . "\n";
								$j .= '<input type="hidden" name="t' . $template->get('id') . '_tags" id="t' . $template->get('id') . '_tags" value="' . $this->escape(stripslashes($tmpltags)) . '" />' . "\n";

								$hi[] = $j;
							}
						?>
					</select>
				</label>
				<?php echo implode("\n", $hi); ?>
			</div>
		</div>

	<?php if ($this->page->access('edit')) { ?>
		<label for="title">
			<?php echo JText::_('COM_WIKI_FIELD_TITLE'); ?>:
			<span class="required"><?php echo JText::_('COM_WIKI_REQUIRED'); ?></span>
			<input type="text" name="page[title]" id="title" value="<?php echo $this->escape($this->page->get('title')); ?>" size="38" />
		</label>
	<?php } else { ?>
		<input type="hidden" name="page[title]" id="title" value="<?php echo $this->escape($this->page->get('title')); ?>" />
	<?php } ?>

		<label for="pagetext" style="position: relative;">
			<?php echo JText::_('COM_WIKI_FIELD_PAGETEXT'); ?>:
			<span class="required"><?php echo JText::_('COM_WIKI_REQUIRED'); ?></span>
			<?php
			echo WikiHelperEditor::getInstance()->display('revision[pagetext]', 'pagetext', $this->revision->get('pagetext'), '', '35', '40');
			?>
		</label>
		<p class="ta-right hint">
			<?php echo JText::sprintf('COM_WIKI_FIELD_PAGETEXT_HINT', JRoute::_('index.php?option=com_wiki&pagename=Help:WikiFormatting')); ?>
		</p>

	<?php if ($this->sub) { ?>
		<div class="field-wrap">
			<div class="grid">
				<div class="col span-half">
					<div id="file-manager" data-instructions="<?php echo JText::_('COM_WIKI_CLICK_OR_DROP_FILE'); ?>" data-action="<?php echo rtrim(JURI::getInstance()->base(true), '/'); ?>/index.php?option=com_wiki&amp;no_html=1&amp;controller=media&amp;task=upload&amp;listdir=<?php echo $lid; ?>" data-list="<?php echo rtrim(JURI::getInstance()->base(true), '/'); ?>/index.php?option=com_wiki&amp;no_html=1&amp;controller=media&amp;task=list&amp;listdir=<?php echo $lid; ?>">
						<iframe name="filer" id="filer" src="<?php echo rtrim(JURI::getInstance()->base(true), '/'); ?>/index.php?option=com_wiki&amp;tmpl=component&amp;controller=media&amp;scope=<?php echo $this->page->get('scope'); ?>&amp;pagename=<?php echo $this->page->get('pagename'); ?>&amp;listdir=<?php echo $lid; ?>"></iframe>
					</div>
					<div id="file-uploader-list"></div>
				</div>
				<div class="col span-half omega">
					<p><?php echo JText::sprintf('COM_WIKI_IMAGE_MACRO_HINT', JRoute::_('index.php?option=' . $this->option . '&scope=' . $this->page->get('scope') . '&pagename=Help:WikiMacros#image')); ?></p>
					<p><?php echo JText::sprintf('COM_WIKI_FILE_MACRO_HINT', JRoute::_('index.php?option=' . $this->option . '&scope=' . $this->page->get('scope') . '&pagename=Help:WikiMacros#file')); ?></p>
				</div>
			</div><!-- / .grid -->
		</div>
	<?php } ?>
	</fieldset><div class="clear"></div>

<?php if (!$this->page->exists() || $this->page->get('created_by') == $juser->get('id') || $this->page->access('manage')) {?>
	<fieldset>
		<legend><?php echo JText::_('COM_WIKI_FIELDSET_ACCESS'); ?></legend>

		<?php if ($this->page->access('edit')) {
			$mode = $this->page->param('mode', 'wiki');
			$cls = '';
			if ($mode && $mode != 'knol')
			{
				$cls = ' class="hide"';
			}

			if (!$this->page->exists() || $this->page->get('created_by') == $juser->get('id') || $this->page->access('manage')) { ?>
				<label for="params_mode">
					<?php echo JText::_('COM_WIKI_FIELD_MODE'); ?>: <span class="required"><?php echo JText::_('COM_WIKI_REQUIRED'); ?></span>
					<select name="params[mode]" id="params_mode">
						<option value="knol"<?php if ($mode == 'knol') { echo ' selected="selected"'; } ?>><?php echo JText::_('COM_WIKI_FIELD_MODE_KNOL'); ?></option>
						<option value="wiki"<?php if ($mode == 'wiki') { echo ' selected="selected"'; } ?>><?php echo JText::_('COM_WIKI_FIELD_MODE_WIKI'); ?></option>
					<?php if ($this->page->access('admin')) { ?>
						<option value="static"<?php if ($mode == 'static') { echo ' selected="selected"'; } ?>><?php echo JText::_('COM_WIKI_FIELD_MODE_STATIC'); ?></option>
					<?php } ?>
					</select>
				</label>
			<?php } else { ?>
				<input type="hidden" name="params[mode]" id="params_mode" value="<?php echo $mode; ?>" />
			<?php } ?>

				<label<?php echo $cls; ?> for="params_authors">
					<?php echo JText::_('COM_WIKI_FIELD_AUTHORS'); ?>:
					<?php
					$mc = $dispatcher->trigger(
						'onGetMultiEntry',
						array(array(
							'members',
							'authors',
							'params_authors',
							'',
							$this->page->authors('string')
						))
					);
					if (count($mc) > 0) {
						echo $mc[0];
					} else { ?>
					<input type="text" name="authors" id="params_authors" value="<?php echo $this->escape($this->page->authors('string')); ?>" />
					<?php } ?>
				</label>

				<label<?php echo $cls; ?>>
					<input class="option" type="checkbox" name="params[hide_authors]" id="params_hide_authors"<?php if ($this->page->param('hide_authors') == 1) { echo ' checked="checked"'; } ?> value="1" />
					<?php echo JText::_('COM_WIKI_FIELD_HIDE_AUTHORS'); ?>
				</label>
				&nbsp;

				<label<?php echo $cls; ?> for="params_allow_changes">
					<input class="option" type="checkbox" name="params[allow_changes]" id="params_allow_changes"<?php if ($this->page->param('allow_changes') == 1) { echo ' checked="checked"'; } ?> value="1" />
					<?php echo JText::_('COM_WIKI_FIELD_ALLOW_CHANGES'); ?>
				</label>

				<label<?php echo $cls; ?> for="params_allow_comments">
					<input class="option" type="checkbox" name="params[allow_comments]" id="params_allow_comments"<?php if ($this->page->param('allow_comments') == 1) { echo ' checked="checked"'; } ?> value="1" />
					<?php echo JText::_('COM_WIKI_FIELD_ALLOW_COMMENTS'); ?>
				</label>
		<?php } else { ?>
				<input type="hidden" name="params[mode]" value="<?php echo $this->page->param('mode', 'wiki'); ?>" />
				<input type="hidden" name="params[allow_changes]" value="<?php echo ($this->page->param('allow_changes') == 1) ? '1' : '0'; ?>" />
				<input type="hidden" name="params[allow_comments]" value="<?php echo ($this->page->param('allow_comments') == 1) ? '1' : '0'; ?>" />
				<input type="hidden" name="authors" id="params_authors" value="<?php echo $this->escape($this->page->authors('string')); ?>" />
				<input type="hidden" name="page[access]" value="<?php echo $this->escape($this->page->get('access')); ?>" />
		<?php } ?>

			<input type="hidden" name="page[group]" value="<?php echo $this->escape($this->page->get('group_cn')); ?>" />

			<?php if ($this->page->access('manage')) { ?>
				<label for="state">
					<input class="option" type="checkbox" name="page[state]" id="state"<?php if ($this->page->isLocked()) { echo ' checked="checked"'; } ?> value="1" />
					<?php echo JText::_('COM_WIKI_FIELD_STATE'); ?>
				</label>
			<?php } ?>
		</fieldset>
		<div class="clear"></div>
<?php } else { ?>
		<input type="hidden" name="page[access]" value="<?php echo $this->escape($this->page->get('access')); ?>" />
		<input type="hidden" name="page[group]" value="<?php echo $this->escape($this->page->get('group_cn')); ?>" />
		<input type="hidden" name="page[state]" value="<?php echo $this->escape($this->page->get('state'), 0); ?>" />
		<input type="hidden" name="authors" value="<?php echo $this->escape($this->page->authors('string')); ?>" />
		<input type="hidden" name="params[mode]" value="<?php echo $this->page->param('mode', 'wiki'); ?>" />
		<input type="hidden" name="params[allow_changes]" value="<?php echo ($this->page->param('allow_changes') == 1) ? '1' : '0'; ?>" />
		<input type="hidden" name="params[allow_comments]" value="<?php echo ($this->page->param('allow_comments') == 1) ? '1' : '0'; ?>" />
<?php } ?>

<?php if ($this->page->access('edit')) { ?>
	<?php if (!$this->sub) { ?>
		<div class="explaination">
			<p><?php echo JText::_('COM_WIKI_FIELD_TAGS_EXPLANATION'); ?></p>
		</div>
	<?php } ?>
		<fieldset>
			<legend><?php echo JText::_('COM_WIKI_FIELDSET_METADATA'); ?></legend>
			<label>
				<?php echo JText::_('COM_WIKI_FIELD_TAGS'); ?>:
				<?php
				$tf = $dispatcher->trigger( 'onGetMultiEntry', array(array('tags', 'tags', 'actags','', $tags)) );
				if (count($tf) > 0) {
					echo $tf[0];
				} else {
					echo '<input type="text" name="tags" value="'. $tags .'" size="38" />';
				}
				?>
				<span class="hint"><?php echo JText::_('COM_WIKI_FIELD_TAGS_HINT'); ?></span>
			</label>
<?php } else { ?>
			<input type="hidden" name="tags" value="<?php echo $this->escape($tags); ?>" />
<?php } ?>

			<label for="field-summary">
				<?php echo JText::_('COM_WIKI_FIELD_EDIT_SUMMARY'); ?>:
				<input type="text" name="revision[summary]" id="field-summary" value="<?php echo $this->escape($this->revision->get('summary')); ?>" size="38" />
				<span class="hint"><?php echo JText::_('COM_WIKI_FIELD_EDIT_SUMMARY_HINT'); ?></span>
			</label>

			<input type="hidden" name="revision[minor_edit]" value="1" />
		</fieldset>
		<div class="clear"></div>

		<input type="hidden" name="page[id]" value="<?php echo $this->escape($this->page->get('id')); ?>" />
		<input type="hidden" name="lid" value="<?php echo $lid; ?>" />
		<input type="hidden" name="pagename" value="<?php echo $this->escape($this->page->get('pagename')); ?>" />

		<input type="hidden" name="revision[id]" value="<?php echo $this->escape($this->revision->get('id')); ?>" />
		<input type="hidden" name="revision[pageid]" value="<?php echo $this->escape($this->page->get('id')); ?>" />
		<input type="hidden" name="revision[version]" value="<?php echo $this->escape($this->revision->get('version')); ?>" />
		<input type="hidden" name="revision[created_by]" value="<?php echo $this->escape($this->revision->get('created_by')); ?>" />
		<input type="hidden" name="revision[created]" value="<?php echo $this->escape($this->revision->get('created')); ?>" />

		<input type="hidden" name="option" value="<?php echo $this->option; ?>" />

		<?php if ($this->sub) { ?>
			<input type="hidden" name="cn" value="<?php echo $this->escape($this->page->get('group_cn')); ?>" />
			<input type="hidden" name="active" value="<?php echo $this->escape($this->sub); ?>" />
			<input type="hidden" name="action" value="save" />
		<?php } else { ?>
			<input type="hidden" name="task" value="save" />
		<?php } ?>

		<?php echo JHTML::_('form.token'); ?>

		<p class="submit">
			<input type="submit" class="btn" name="preview" value="<?php echo JText::_('COM_WIKI_PREVIEW'); ?>" /> &nbsp;
			<input type="submit" class="btn btn-success" name="submit" value="<?php echo JText::_('COM_WIKI_SUBMIT'); ?>" />
		</p>
	</form>
</section><!-- / .main section -->