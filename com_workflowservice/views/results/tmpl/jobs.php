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
<!-- DataTables CSS -->
<link rel="stylesheet" type="text/css" href="/media/DataTables-1.10.1/css/jquery.dataTables.css">
  
<!-- jQuery -->
<script type="text/javascript" charset="utf8" src="/media/DataTables-1.10.1/js/jquery.js"></script>
  
<!-- DataTables -->
<script type="text/javascript" charset="utf8" src="/media/DataTables-1.10.1/js/jquery.dataTables.js"></script>
<div id="content-header" class="full">
	<h2>My Jobs</h2>
</div><!-- / #content-header -->

<script type="text/javascript" >
	$(document).ready( function () {
		$('#jobs').dataTable({
		 "order": [[ 5, "desc" ]]
		 });
	});
</script>

<style>
	.job_error { padding: 3px; background: red; color: white; }
</style>


<?php }

	echo "<table border='1' cellpadding='1' cellspacing='0' id='jobs'>\n";
	echo "<thead>\n";
	echo "<tr><th>Job Name</th><th>ID</th><th>Workflow Name</th><th>Owner</th><th>Status</th><th>Created</th><th></th></tr>\n";
	echo "</thead>\n";
	foreach ($this->tasks as $task) {
		if (isset($task->workflow->name) ) {
			$workflow_name = $task->workflow->name;
			$workflow_version = $task->workflow->version;
		} else {
			$workflow_name = 'Unknown';
			$workflow_version = 'unknown';
		}	

		echo "<tr>\n";
		echo "<td><a href='jobDetails/" . $task->id . "' target='_blank'>$task->name</a></td>";
		echo "<td>$task->id</td>";
		echo "<td>$workflow_name (version " . $workflow_version. ")</td>";
		echo "<td>$task->owner</td>";
		
		if ($task->status == 'Error')
			echo "<td><span class='job_error'>$task->status</span></td>";
		else
			echo "<td>$task->status</td>";
			
		echo "<td>" . gmdate('Y-m-d H:i:s', ($task->createDate/1000)) . "</td>"; // output = 2012-08-15 00:00:00
		echo '<td><a href="javascript:return(0);"><img src="' . JURI::root(). 'components/com_workflowservice/assets/img/DeleteRed.png" width="12px" class="testthing" data-stateid="' . $task->id . '" id="remove' . $task->id . '"  border="0" /></a></td>' . "\n";
		echo "</tr>\n";
	}
	echo "</table>\n";
?>

<script type="text/javascript">
$(document).on('click', '.testthing', function(e) {

    var tr = $(this).closest('tr');
    var stateId = $(this).data('stateid');
	$.ajax({
		contentType: "application/json; charset=utf-8",
		url: '/workflowservice/deleteJob/' + stateId,
		type: 'POST',
		data: '{"flag":"deleted"}',
		dataType: "json",
		success: function (data) { 
			tr.slideUp('slow', function() { 
				// now that you have slided Up, let's remove it from the DOM
				$(this).remove(); 
			});
			alert("The job has been deleted. "); },
		error:  function (data) { alert("There was an error when deleting your job. " + stateId);}
	});
});
</script>
