<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

?>

<style type="text/css">
	.st_WorkspaceSync { background: yellow; padding: 4px; color: #666; }
	.st_Error { background: red; padding: 4px; color: #ccc;  }
	.st_Running, st_InQueue, st_Pending { background: green; padding: 4px; }
	.st_Paused { background: orange; padding: 4px; color: #666; }
	.st_Completed { }
</style>	


	<header id="content-header">
		<h2>My Jobs</h2>
	</header><!-- / #content-header -->
	
	<section class="main section">


<script type="text/javascript" >
	$(document).ready( function () {
		var table = 	$('#jobs').DataTable( {
				"processing": true,
				"serverSide": false,
				"ajax": "/workflowservice/jobsJSON",
				"columns": [
					{ "data": "name",
						"render": function(data,type,row,meta) {
							var a = '<a href="/workflowservice/jobdetails/' + row.id + '" target="_blank">' + row.name + '</a>'
							return a;
						}
					},
					{ "data": "id" },
					{ "data": "workflow_with_version"},
					{ "data": "owner" },
					{ "data": "status",
						"render": function(data,type,row,meta) {
							switch (row.status) {
								case "Workspace Sync":
									var tooltip = "Your job is ready to run, pending synchronization of workspace files";
									break;

								case "Pending":
									var tooltip = "Your job is ready to run, pending availability of resources";
									break;

								case "In Queue":
									var tooltip = "Your job is in the queue, awaiting completion of your current running or pending job(s). Jobs in this state may also be held by the system and will automatically resume (e.g. jobs are held for system maintenance)";
									break;

								case "Running":
									var tooltip = "Your job is running";
									break;

								case "Error":
									var tooltip = "Your job has failed due to a detected error";
									break;

								case "Paused":
									var tooltip = "Your job has been paused by the system and will automatically resume (e.g. jobs are paused for system maintenance)";
									break;

								case "Completed":
									var tooltip = "";
									break;
							}		
									
							var a = '<span class="st_' + row.status.replace(/\s+/g, '') + '" title="' + tooltip + '">' + row.status + "</span>\n";
							return a;
						}
					},{ "data": "createDate" }
				],
			   "columnDefs": [
					{
						// The `data` parameter refers to the data for the cell (defined by the
						// `data` option, which defaults to the column being worked with, in
						// this case `data: 0`.
						"render": function ( data, type, row ) {
							return formatDate(new Date(data - (420 * 60 * 1000)), '%Y-%M-%d %H:%m:%s');
						},
						"targets": 5
					}
					 
				],
				"sort": true,    
				"order": [[ 5, "desc" ]]
			});
					
		setInterval( function () {
			table.ajax.reload( null, false ); // user paging is not reset on reload
			}, 30000 );
			
	});
</script>

<style>
	.job_error { padding: 3px; background: red; color: white; }
</style>

<?php
	echo "<table border='1' cellpadding='1' cellspacing='0' id='jobs'>\n";
	echo "<thead>\n";
	echo "<tr><th>Job Name</th><th>ID</th><th>Workflow Name</th><th>Owner</th><th>Status</th><th>Created</th><th></th></tr>\n";
	echo "</thead>\n";
	echo "<tbody>\n";
	echo "</tbody>\n";
	echo "</table>\n";
	echo "</section>\n";
	
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

function formatDate(date, fmt) {
		function pad(value) {
			return (value.toString().length < 2) ? '0' + value : value;
		}
		return fmt.replace(/%([a-zA-Z])/g, function (_, fmtCode) {
			switch (fmtCode) {
			case 'Y':
				return date.getUTCFullYear();
			case 'M':
				return pad(date.getUTCMonth() + 1);
			case 'd':
				return pad(date.getUTCDate());
			case 'H':
				return pad(date.getUTCHours());
			case 'm':
				return pad(date.getUTCMinutes());
			case 's':
				return pad(date.getUTCSeconds());
			default:
				throw new Error('Unsupported format code: ' + fmtCode);
			}
		});
	}

function jooo(data) {
	return data.name;
}		
		</script>