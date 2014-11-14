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

?>

<script type="text/javascript">
$(document).ready(function() {
    $('#example').dataTable( {
        "processing": true,
        "serverSide": false,
        "ajax": "WorkspaceFilesJSON",
        "columns": [
            { "data": "name" },
            { "data": "id" },
            { "data": "type" },
            { "data": "size" },
            { "data": "owner" },
			{ "data" : "createDate" }
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
        
    } );
    
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
