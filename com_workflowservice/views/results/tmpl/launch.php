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
<style>
	.selected, .highlight_row { background: pink; }
	.alpaca-controlfield-label {font-size: 1.1em;}
	.minHelperText {width: 900px; white-space: normal; }
	.ui-icon {width: 16px;height: 16px;display: block;background-image: url(/media/system/images/ui/ui-icons_454545_256x240.png);float: left;}
	.ui-icon.large {background-size: 384px 360px;width: 24px;height: 24px;margin-top: 0;}

	.ui-icon-triangle-1-e.large { background-position: -48px -24px; }
	.ui-icon-triangle-1-s.large {     background-position: -96px -24px; }

	#advanced { padding-bottom: 20px; display: none; }
	.advanced_title {font-size: 1.2em; font-weight: bold; }
	#adv1 { padding-left: 20px; display: none; }
	
	#boxo { }
	#lefto { position: relative; float: left; width: 600px; }
	#righto { position: relative; float: left; width: 450px; }
</style>

	<header id="content-header">
		<h2><?php echo $this->workflow_name; ?></h2>
	</header><!-- / #content-header -->
	
	<section class="main section">
		<div id="boxo">
			<div id="lefto">
				<div><?php echo nl2br($this->workflow_description); ?></div>

				<form name="myform" id="myform">

				<div id="form"></div>
				<?php
				if (isset($this->alpaca_adv_id)) { ?>
				<div id="advanced">
					<i class="ui-icon ui-icon-triangle-1-e large"></i>
					<span class="advanced_title">Advanced Parameters</span>
			
					<div id="adv1"></div>
				</div>
				<?php } ?>
			
			<div id="errors"></div>
			
			</div>
			<div id="righto">
				<div class="advanced_title">Release Notes</div>
				<?php echo nl2br($this->release_notes); ?>
			</div>
		</div>
		
							
		</form>
	</section>

<script type="text/javascript">
$(document).ready(function() {
<?php if (isset($this->alpaca_file_id)) { 
	echo 'var wf_array = ' . $this->alpaca_wf_array . ";\n";
	echo 'var alpaca_file_ids = '. $this->alpaca_file_id . ";\n";
} else {
	echo "var wf_array = new Array();";
	}

	 if (isset($this->alpaca_adv_id)) { 
//	echo 'var adv_array = ' . json_encode($adv_array) . ";\n";
	echo 'var alpaca_adv_ids = '. $this->alpaca_adv_id . ";\n";
}

//	echo '	var data = ' . json_encode($data_array) . ";\n";
//	echo '	var schema = ' . json_encode($o_schema) . ";\n";
//	echo '	var options = ' . json_encode($o_options) . ";\n";
	echo '	var postRenderCallback = function(control) { }; ' . "\n";
?>

	$("#form").alpaca({
		"data": <?php echo $this->alpaca_data; ?>,
		"schema": <?php echo $this->alpaca_schema; ?>,
		"options": {
            "renderForm": true,
            "form": {
				"attributes": {
					"method": "POST",
					"action": "/workflowservice/processJSON",
					"enctype": "multipart/form-data"
				},
                "buttons": {
					"submit":{
						"title": "Send Form Data",
						"value": "Submit Job"
					}
                }
            },
			<?php echo $this->alpaca_options; ?>
		},
		
		"postRender": function(renderedField) {
		// helper text won't wrap, so convert span to div and add the .minHelperText
			$(".alpaca-controlfield-helper span").replaceWith(function() { return "<div class='minHelperText'>" + this.innerHTML + "</div>"; });

			var skipwf;
			$.each(wf_array, function(number, val) {
				// get JSON list of wf files for the specified file field
			
				$.getJSON( "../WorkspaceFilesJSON?" + val , function( data ) {
					$.each( data, function( key, val ) {
						if ((key == 'data') && (val != "")) {
							skipwf = true;
						}
					});
				});

				if (skipwf) {
					$("#alpaca" + number + "-controlfield-helper")
					.prepend('<div><strong>No workspace files exist ...</strong></div>')

				} else {
					$("#alpaca" + number).hide();
			
					$("#alpaca" + number + "-controlfield-helper")
						.prepend('<div id="selectfile' + number + '"><input type="button" value="Choose File"> <span id="showfileID' + number + '"></span><span id="fileID' + number + '"></span></div>') // Create the element
						.prepend( "<div id='apply" + number + "'><input type='button' value='Apply' id='applyfile" + number + "'></div>" )
						.prepend( "<div id='workflowfiles" + number + "'><table id='files" + number + "' class='display' cellspacing='0' width='100%'><thead><tr><th>Name</th><th>ID</th><th>Type</th><th>Size</th><th>Owner</th><th>Create Date</th></tr></thead></table></div>" )
						.button() // Ask jQuery UI to buttonize it
						$("#apply" + number).hide();

					$("#workflowfiles" + number).hide();
					$("#selectfile" + number).click(function(){  // Add a click handler
						$("#workflowfiles" + number).show();
						$("#selectfile" + number).hide();
						$("#apply" + number).show();

					}); 

					$('#files' + number).dataTable( {
						"processing": true,
						"serverSide": false,
						"ajax": "../WorkspaceFilesJSON?" + wf_array,
						"columns": [
							{ "data": "name" },
							{ "data": "id" },
							{ "data": "type" },
							{ "data": "size" },
							{ "data": "owner" },
							{ "data": "createDate" }
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
						"order": [[ 5, "desc" ]],
						"rowCallback": function( row, data, displayIndex ) {
							if ( $.inArray(data.DT_RowId, selected) !== -1 ) {
								$(row).addClass('selected');
							}
						}
					});
				
					var selected = [];
					var table = $('#files' + number).DataTable();
 
					// highlight selected row, unhighlight prev selected row, pass selected file info to form
					$('#files' + number + ' tbody').on( 'click', 'tr', function () {
						if ( $(this).hasClass('selected') ) {
							$(this).removeClass('selected');
						}
						else {
							table.$('tr.selected').removeClass('selected');
							$(this).addClass('selected');
	//						 $("#showfileID" + number).html(this.id) ;
	//						 var passID = this.id;

							var tableData = $(this).children("td").map(function() {
								return $(this).text();
							}).get();

							// create hidden form variable for filename, ie replace the original version
							$.each( alpaca_file_ids, function( key, value, filename ) {
								if (number == value) {
									 $("#showfileID" + number).html(tableData[0] + " (" + tableData[1] + ")") ;
									 $("#fileID" + number).html('<input type="text" name="' + key + '" value="' + tableData[1] + '"><input type="hidden" name="isFile_' + key + '" value="1">') ;
									 $("#alpaca" + number).val(tableData[1]);
								}
							});	
						}
					} );
 
					$('#button' + number).click( function () {
						table.row('.selected').remove().draw( false );
					});

					$("#apply" + number)
						.click(function(){ 
							$("#workflowfiles" + number).hide();
							$("#apply" + number).hide();
							$("#selectfile" + number).show();
					

						}); // Add a click handler
					
					$("#selectfile" + number).click(function(){  // Add a click handler
						$("#workflowfiles" + number).show();
						$("#selectfile" + number).hide();
							$("#apply" + number).show();
						}); // Add a click handler
				
									
				}	
			});

			if (!(jQuery.isEmptyObject(alpaca_adv_ids))) {
			// if advanced parameters, move the #advanced block to below Submit button
			// {"Stage":7,"Level":8,"createCHMTrainJob":10};
				// handle display of advanced parameters
				$.each(alpaca_adv_ids, function(number, val) {
					$('#alpaca' + val + '-item-container')
						.appendTo("#adv1");

				// move the #advanced div to a place before the submit button
				$("#advanced")
					.show()
					.prependTo('#alpaca2-form-buttons-container')
				});
				
				// toggle the adv param icon and display
				$('.ui-icon').click( function () {
					$("#adv1").toggle();
					$("i",this).toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s");
				});
			}	
		
		
		
		



            var form = renderedField.form;
            if (form) {
                form.registerSubmitHandler(function(e, form) {
                    // validate the entire form (top control + all children)
                    form.validate(true);
                    // draw the validation state (top control + all children)
                    form.refreshValidationState(true);
                    // now display something


                   		obj = jQuery.parseJSON(' <?php echo $this->alpaca_req_file_array; ?> ');
						$.each (obj, function(a, b) {
							if (!($("#alpaca" + b).val().length)) {
								alert ("You must select a file");
							}
						});


                    if (form.isFormValid()) {
                        var value = form.getValue();
                    } else {

                        alert("There are problems with the form.  Please make the any necessary corrections.");
                    }
                    e.stopPropagation();
                    return false;
                });
            }
        }

		
		, // close postRender function
        "view": "VIEW_WEB_EDIT"
    });  // close of alpaca form


						
		
		$("#alpaca2").submit(function(e)
		{
			var postData = $(this).serializeArray();
			var formURL = $(this).attr("action");
			$.ajax(
			{
				url : formURL,
				type: "POST",
				data : postData,
				success:function(data, textStatus, jqXHR) {
					var obj = jQuery.parseJSON( data );
					if (obj.status === "success") {
						$( location ).attr("href", "/workflowservice/jobs");
					} else {
						obj.reason = obj.reason.replace(/<br \/>/g, "\n");
						alert("There was an error with your job submission: \n" + obj.reason.replace(/&quot;/g,'"'))
					}	
				},
				error: function(jqXHR, textStatus, errorThrown) 
				{
				}
			});
			e.preventDefault();	//STOP default action
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
</script>	

<?php 

function textToString ($input) {
	if ($input == 'text') 
		return 'string';
	if ($input == 'textarea') 
		return 'string';
	if ($input == 'number') 
		return 'string';
	if ($input == 'hidden')
		return 'string';		
	if ($input == 'file')
		return 'string';		
}

?>


		

