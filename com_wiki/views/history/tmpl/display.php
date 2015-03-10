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
?>
	<header id="<?php echo ($this->sub) ? 'sub-content-header' : 'content-header'; ?>">
		<h2><?php echo $this->escape($this->title); ?></h2>
		<?php
		if (!$this->page->isStatic())
		{
			$this->view('authors', 'page')
			     ->setBasePath($this->base_path)
			     ->set('page', $this->page)
			     ->display();
		}
		?>
	</header><!-- /#content-header -->

<?php if ($this->getError()) { ?>
	<p class="error"><?php echo $this->getError(); ?></p>
<?php } ?>

<?php if ($this->message) { ?>
	<p class="passed"><?php echo $this->message; ?></p>
<?php } ?>

<?php
	$this->view('submenu', 'page')
	     ->setBasePath($this->base_path)
	     ->set('option', $this->option)
	     ->set('controller', $this->controller)
	     ->set('page', $this->page)
	     ->set('task', $this->task)
	     ->set('sub', $this->sub)
	     ->display();
?>

<section class="main section">
	<div class="section-inner">
		<div class="grid">
			<div class="col span-half">
				<p><?php echo JText::sprintf('COM_WIKI_HISTORY_EXPLANATION', JRoute::_('index.php?option=' . $this->option . '&scope=' . $this->page->get('scope') . '&pagename=Help:PageHistory')); ?></p>
			</div><!-- / .aside -->
			<div class="col span-half omega">
				<p>
					<?php echo JText::_('COM_WIKI_HISTORY_CUR_HINT'); ?><br />
					<?php echo JText::_('COM_WIKI_HISTORY_LAST_HINT'); ?>
				</p>
			</div><!-- / .subject -->
		</div>

		<form action="<?php echo JRoute::_($this->page->link('compare')); ?>" method="post">
			<p class="info">
				<?php echo JText::sprintf('COM_WIKI_HISTORY_SUMMARY', count($this->page->revisions()), '<time datetime="' . $this->page->get('created') . '">' . $this->page->get('created') . '</time>', '<time datetime="' . $this->page->get('modified') . '">' . $this->page->get('modified') . '</time>'); ?>
			</p>

			<div class="container">
				<p><input type="submit" value="<?php echo JText::_('COM_WIKI_HISTORY_COMPARE'); ?>" /></p>

				<table class="entries" id="revisionhistory">
					<caption><?php echo JText::_('COM_WIKI_HISTORY_TBL_SUMMARY'); ?></caption>
					<thead>
						<tr>
							<th scope="col"><?php echo JText::_('COM_WIKI_HISTORY_COL_VERSION'); ?></th>
							<th scope="col" colspan="2"><?php echo JText::_('COM_WIKI_HISTORY_COL_COMPARE'); ?></th>
							<th scope="col"><?php echo JText::_('COM_WIKI_HISTORY_COL_WHEN'); ?></th>
							<th scope="col"><?php echo JText::_('COM_WIKI_HISTORY_COL_MADE_BY'); ?></th>
							<th scope="col"><?php echo JText::_('COM_WIKI_HISTORY_COL_LENGTH'); ?></th>
							<th scope="col"><?php echo JText::_('COM_WIKI_HISTORY_COL_STATUS'); ?></th>
						<?php if (($this->page->isLocked() && $this->page->access('manage')) || (!$this->page->isLocked() && $this->page->access('delete'))) { ?>
							<th scope="col"></th>
						<?php } ?>
						</tr>
					</thead>
					<tbody>
					<?php
					$i = 0;
					$cur = 0;
					$cls = 'even';
					foreach ($this->page->revisions('list', array('sortby' => 'version DESC'), true) as $revision)
					{
						$i++;

						$cls = ($cls == 'odd') ? 'even' : 'odd';
						$level = ($revision->get('minor_edit')) ? 'minor' : 'major';

						$xname = $revision->creator('name') ? $revision->creator('name') : JText::_('COM_WIKI_AUTHOR_UNKNOWN');

						$summary = ($revision->get('summary')) ? $this->escape($revision->get('summary')) : JText::_('COM_WIKI_REVISION_NO_SUMMARY');

						switch ($revision->get('approved'))
						{
							case 1: $status = 'approved'; break;
							case 0:
							default:
								$status = 'suggested';
								break;
						}

						$prvLength = 0;
						$this->page->revisions()->next();
						$nxt = $this->page->revisions()->current();
						if ($nxt)
						{
							$prvLength = $nxt->get('length');
						}
						$this->page->revisions()->prev();

						$diff = $revision->get('length') - $prvLength;
					?>
							<tr class="<?php echo $cls; ?>">
								<td>
									<?php if ($this->page->revisions()->isFirst()) {
										$cur = $revision->get('version'); ?>
										( cur )
									<?php } else { ?>
										(<a href="<?php echo JRoute::_($this->page->link('compare') . '&oldid=' . $revision->get('version') . '&diff=' . $cur); ?>">
											<?php echo JText::_('COM_WIKI_HISTORY_CURRENT'); ?>
										</a>)
									<?php } ?>
										&nbsp;
									<?php if (!$this->page->revisions()->isLast()) { ?>
										(<a href="<?php echo JRoute::_($this->page->link('compare') . '&oldid=' . ($revision->get('version') - 1) . '&diff=' . $revision->get('version')); ?>">
											<?php echo JText::_('COM_WIKI_HISTORY_LAST'); ?>
										</a>)
									<?php } else { ?>
										( last )
									<?php } ?>
								</td>
							<?php if ($i == 1) { ?>
								<td>

								</td>
								<td>
									<input type="radio" name="diff" value="<?php echo $revision->get('version'); ?>" checked="checked" />
								</td>
							<?php } else { ?>
								<td>
									<input type="radio" name="oldid" value="<?php echo $revision->get('version'); ?>"<?php if ($i == 2) { echo ' checked="checked"'; } ?> />
								</td>
								<td>

								</td>
							<?php } ?>
								<td>
									<a href="<?php echo JRoute::_($this->page->link() . '&version=' . $revision->get('version')); ?>" class="tooltips" title="<?php echo JText::_('COM_WIKI_REVISION_SUMMARY').' :: ' . $summary; ?>">
										<time datetime="<?php echo $revision->get('created'); ?>"><?php echo $this->escape($revision->get('created')); ?></time>
									</a>
									<a class="tooltips markup" href="<?php echo JRoute::_($this->page->link() . '&version=' . $revision->get('version') . '&format=raw'); ?>" title="<?php echo JText::_('COM_WIKI_HISTORY_MARKUP_TITLE'); ?>">
										<?php echo JText::_('COM_WIKI_HISTORY_MARKUP'); ?>
									</a>
								</td>
								<td>
									<?php echo $this->escape($xname); ?>
								</td>
								<td>
									<?php echo JText::sprintf('COM_WIKI_HISTORY_BYTES', number_format($revision->get('length'))); ?> (<span class="page-length <?php echo ($diff > 0) ? 'increase' : ($diff == 0 ? 'created' : 'decrease'); ?>"><?php echo ($diff > 0) ? '+' . number_format($diff) : ($diff == 0 ? number_format($diff) : number_format($diff)); ?></span>)
								</td>
								<td>
									<?php echo $this->escape($status); ?>
									<?php if (!$revision->get('approved') && $this->page->access('manage')) { ?>
										<br />
										<a href="<?php echo JRoute::_($this->page->link('approve') . '&oldid=' . $revision->get('id')); ?>">
											<?php echo JText::_('COM_WIKI_ACTION_APPROVED'); ?>
										</a>
									<?php } ?>
								</td>
							<?php if (($this->page->isLocked() && $this->page->access('manage')) || (!$this->page->isLocked() && $this->page->access('delete'))) { ?>
								<td>
									<a class="delete" href="<?php echo JRoute::_($this->page->link('deleterevision') . '&oldid=' . $revision->get('id')); ?>" title="<?php echo JText::_('COM_WIKI_REVISION_DELETE'); ?>">
										<?php echo JText::_('JACTION_DELETE'); ?>
									</a>
								</td>
							<?php } ?>
							</tr>
					<?php } ?>
					</tbody>
				</table>
				<p><input type="submit" value="<?php echo JText::_('COM_WIKI_HISTORY_COMPARE'); ?>" /></p>
			</div><!-- / .container -->

			<div class="clear"></div>

			<input type="hidden" name="pagename" value="<?php echo $this->escape($this->page->get('pagename')); ?>" />
			<input type="hidden" name="scope" value="<?php echo $this->escape($this->page->get('scope')); ?>" />
			<input type="hidden" name="pageid" value="<?php echo $this->escape($this->page->get('id')); ?>" />
			<input type="hidden" name="option" value="<?php echo $this->escape($this->option); ?>" />
			<input type="hidden" name="controller" value="<?php echo $this->escape($this->controller); ?>" />
			<?php if ($this->sub) { ?>
				<input type="hidden" name="active" value="<?php echo $this->escape($this->sub); ?>" />
				<input type="hidden" name="action" value="compare" />
			<?php } else { ?>
				<input type="hidden" name="task" value="compare" />
			<?php } ?>
		</form>
	</div>
</section><!-- / .main section -->