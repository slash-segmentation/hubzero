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


if (!$this->no_html) { ?>
<div id="content-header" class="full">
	<h2>Job Details</h2>
</div><!-- / #content-header -->

<?php } ?>

<style>
.form-element label {
    display: inline-block;
    width: 240px;
    padding-left: 20px;
}
</style>

<div class="form-element">
  <label for="name">Name</label><?php echo $this->task->name; ?><br />
  <label for="id">ID</label><?php echo $this->task->id; ?><br />
  <label for="owner">Owner</label><?php echo $this->task->owner; ?><br />
  <label for="status">Status</label><?php echo $this->task->status; ?><br />
  <label for="jobId">job ID</label><?php echo $this->task->jobId; ?><br />
  
  <label for="createDate">Create Date</label><?php echo formatDate($this->task->createDate); ?><br />
  <label for="submitDate">Submit Date</label><?php echo formatDate($this->task->submitDate); ?><br />
  <label for="finishDate">Finish Date</label><?php echo formatDate($this->task->finishDate); ?><br />

  <label for="estimatedCpuInSeconds">Estimated CPU (s)</label><?php echo $this->task->estimatedCpuInSeconds; ?><br />
  <label for="estimatedRunTime">Estimated run time</label><?php echo $this->task->estimatedRunTime; ?><br />
  <label for="hasJobBeenSubmittedToScheduler">Job submitted to scheduler?</label><?php echo $this->task->hasJobBeenSubmittedToScheduler ? 'yes' : 'no'; ?><br />
  <label for="downloadURL">Download URL</label><?php echo $this->task->downloadURL; ?><br />
  <label for="summaryOfErrors"> Summary of Errors</label><?php echo $this->task->summaryOfErrors; ?><br />
</div>

<?php
function formatDate($date) {
	if (strlen($date))
		return gmdate('Y-m-d H:i:s', $date/1000);
	else 
		return '';
}
?>