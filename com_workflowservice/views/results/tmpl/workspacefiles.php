<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

?>

<script type="text/javascript">
$(document).ready(function() {
var table = 	$('#example').DataTable( {
        "processing": true,
        "serverSide": false,
        "ajax": "WorkspaceFilesJSON",
        "columns": [
            { "data": "name" },
            { "data": "id" },
            { "data": "type" },
            { "data": "size" },
            { "data": "owner" },
			{ "data" : "formatted_createDate" }
        ],
       "columnDefs": [
            {
				// The `data` parameter refers to the data for the cell (defined by the
				// `data` option, which defaults to the column being worked with, in
				// this case `data: 0`.
				"render": function ( data, type, row ) {
					return formatSize(data);
				},
				"targets": 3
			}            
        ],
		"sort": true,    
		"order": [[ 5, "desc" ]]
	});
					
	setInterval( function () {
		table.ajax.reload( null, false ); // user paging is not reset on reload
		}, 3600000 );

			
    $("#addFile").click(function(){
		$("#myFile").removeClass("hidden");
	});

    $("#submitFile").click(function(){
    	alert("GO");
    	var filename = $("#filename").val();
//		$("#myFile").addClass("hidden");

        $.ajax({
            type: "POST",
            url: "/workflowservice/upload",
            enctype: 'multipart/form-data',
            data: {
                file: filename
            },
            success: function () {
                alert("Data Uploaded: ");
            }
        });
	});

	function formatSize(bytesize) {
		var msize = bytesize/1024/1024;

		if (msize < 1) {
			return msize.toFixed(4) + "M";
		} else if (msize < 10) {	
			return msize.toFixed(3) + "M";
		} else if (msize < 100) {	
			return msize.toFixed(2) + "M";
		} else if (msize < 1000) {	
			return msize.toFixed(1) + "M";
		} else {	
			return msize.toFixed(0) + "M";
		}	
			
		/*
		Old code in case the numerical sort doesn't matter as much; looks nicer
		if (bytesize < 1024) {
			return "< 1k";
		} else {
			var msize = bytesize/1024/1024;
			return msize.toFixed(2) + "M";
		}
		*/
	}
    
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
});
</script>

	<header id="content-header">
		<h2>Workspace Files</h2>
	</header><!-- / #content-header -->
	
	<section class="main section">

		<table id="example" class="display" cellspacing="0" width="100%">
			<thead>
				<tr>
					<th>Name</th>
					<th>ID</th>
					<th>Type</th>
					<th>Size</th>
					<th>Owner</th>
					<th>Create Date</th>
				</tr>
			</thead>
 
			<tfoot>
				<tr>
					<th>Name</th>
					<th>ID</th>
					<th>Type</th>
					<th>Size</th>
					<th>Owner</th>
					<th>Create Date</th>
				</tr>
			</tfoot>
		</table>
   </section> 
