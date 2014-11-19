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

	$hidden = array();
	class o_schema {};
	class o_options {};
	class o_data {};
	
	$schema_array = array(); // for schema properties
	$option_array = array(); // for option fields
	$data_array = array(); // for data
	
	if (isset($this->alpaca_file_id))
		$js_file_ids = json_decode($this->alpaca_file_id);

	if (isset($this->alpaca_adv_id))
		$js_adv_ids = json_decode($this->alpaca_adv_id);

	foreach ($this->workflow->parameters as $wfp) {
		// deal with advanced parameters ; this should populate adv_array and alpaca_adv_ids
		if ($wfp->isAdvanced == true) {
			$wf_split = explode("?", $wfp->value);
			$tmp = str_replace("[form]", "", $wfp->name);

			$adv_array[$js_adv_ids->$tmp] = $wf_split[1];
		}
	
		$option_array[$wfp->name]['fieldClass'] = 'myClass';
	
	
	
		$wfp->name = "[form]" . $wfp->name;
		if ($wfp->name == '[form]CWS_notifyemail')
			$data_array[$wfp->name] = $this->notifyemail;
		else
			$data_array[$wfp->name] = $wfp->value;
		
		// Default schema properties will be "string"
//			$schema_array[$wfp->name] = array('type'=>textToString($wfp->type));
		$schema_array[$wfp->name]['type'] = 'string';
			
		// All fields will get a "Label", except for hidden ones	
		if ($wfp->type == 'hidden') {
			if ($wfp->name == '[form]CWS_jobname') {
				$option_array[$wfp->name] = array('label'=>'Job Name');
				$data_array[$wfp->name] = $wfp->value;
//				$option_array[$wfp->name]['type'] = 'string';
			} else 
				$option_array[$wfp->name]['type'] = 'hidden';
		} else	
			$option_array[$wfp->name] = array('label'=>$wfp->displayName);
			

		$option_array[$wfp->name]['helper'] = $wfp->help;

		switch ($wfp->type) {
			case "textarea":
					$option_array[$wfp->name]['type'] = 'textarea';
					$option_array[$wfp->name]['rows'] = $wfp->rows;
					$option_array[$wfp->name]['cols'] = $wfp->columns;
					$data_array[$wfp->name] = $wfp->value;
				break;
				
			case "file":
				$schema_array[$wfp->name]['format'] = 'uri';
				$option_array[$wfp->name]['type'] = 'file';
				$wf_split = explode("?", $wfp->value);
				$tmp = str_replace("[form]", "", $wfp->name);
				if (isset($this->alpaca_file_id))
					$wf_array[$js_file_ids->$tmp] = $wf_split[1];
				break;

			case "text":
				if ($wfp->validationType == 'digits')
					$option_array[$wfp->name]['type'] = 'integer';
				elseif ($wfp->validationType == 'number')
					$option_array[$wfp->name]['type'] = 'number';
				elseif (($wfp->validationType == 'email') && ($wfp->isRequired)) {
					$option_array[$wfp->name]['type'] = 'email';
					$option_array[$wfp->name]['allowOptionalEmpty'] = true;
				}	
					
				if ($option_array[$wfp->name]['type'] !== 'string') {	
					$option_array[$wfp->name]['validate'] = true;
					$schema_array[$wfp->name]['minimum'] = $wfp->minValue;
					$schema_array[$wfp->name]['maximum'] = $wfp->maxValue;
					$schema_array[$wfp->name]['pattern'] = $wfp->validationRegex;
				}	
				break;
				
			case "dropdown":
				$option_array[$wfp->name]['type'] = 'select';
				$option_array[$wfp->name]['dataSource'] = $wfp->valueMap;
				$option_array[$wfp->name]['removeDefaultNone'] = true;
				$data_array[$wfp->name] = $wfp->selected;
				break;
				
			case "checkbox":
				$option_array[$wfp->name]['rightLabel'] = $wfp->displayName;
				$option_array[$wfp->name]['type'] = 'checkbox';
				unset($option_array[$wfp->name]['label']);
				break;
		}
	}

	// If CWS_jobname is given in the JSON, it was handled above. If not in JSON, handle here
	$cws = '[form]CWS_jobname';
	if (!(isset($option_array[$cws]))) {	
		$option_array[$cws] = array('label'=>'Job Name');
		$data_array[$cws] = '';
		$schema_array[$cws]['type'] = 'string';
	}	

	$cws = '[form]CWS_user';
	if (!(isset($option_array[$cws]))) {	
		$data_array[$cws] = $this->owner;
	}	

	/* override owner = user in the CHM */
	 $data_array[$cws] = $this->owner;
	
	$o_schema->type = 'object';
	$o_schema->properties = $schema_array;
	$o_options->fields = $option_array;

?>
<style>
	.selected, .highlight_row { background: pink; }
	.alpaca-controlfield-label {font-size: 1.1em;}
	.minHelperText {width: 900px; white-space: normal; }
	.ui-icon {width: 16px;height: 16px;display: block;background-image: url(/media/system/images/ui/ui-icons_454545_256x240.png);float: left;}
	.ui-icon.large {background-size: 384px 360px;width: 24px;height: 24px;margin-top: 0;}

	.ui-icon-triangle-1-e.large { background-position: -48px -24px; }
	.ui-icon-triangle-1-s.large {     background-position: -96px -24px; }

	#advanced { padding-bottom: 20px; }
	.advanced_title {font-size: 1.2em; font-weight: bold; }
	#adv1 { padding-left: 20px; display: none;}
</style>

	<header id="content-header">
		<h2><?php echo $this->workflow->name; ?></h2>
	</header><!-- / #content-header -->
	
	<section class="main section">
		<div><?php echo nl2br($this->workflow->description); ?></div>

		<form method="post" action="/workflowservice/process" enctype="multipart/form-data" id="myform">
		<input type='hidden' name='workflowID' value = '<?php echo $this->workflow->id ?>' />
		<div id="form"></div>
		<?php
		if (isset($this->alpaca_adv_id)) { ?>
		<div id="advanced">
			<i class="ui-icon ui-icon-triangle-1-e large"></i>
			<span class="advanced_title">Advanced Parameters</span>
			
			<div id="adv1"></div>
		</div>
		<?php } ?>
		<input type="submit" name="submit_job">
		</form>
	</section>

<script type="text/javascript">
$(document).ready(function() {
<?php if (isset($this->alpaca_file_id)) { 
	echo 'var wf_array = ' . json_encode($wf_array) . ";\n";
	echo 'var alpaca_file_ids = '. $this->alpaca_file_id . ";\n";
}

	 if (isset($this->alpaca_adv_id)) { 
	echo 'var adv_array = ' . json_encode($adv_array) . ";\n";
	echo 'var alpaca_adv_ids = '. $this->alpaca_adv_id . ";\n";
}

	echo '	var data = ' . json_encode($data_array) . ";\n";
	echo '	var schema = ' . json_encode($o_schema) . ";\n";
	echo '	var options = ' . json_encode($o_options) . ";\n";
	echo '	var postRenderCallback = function(control) { }; ' . "\n";
?>

	$("#form").alpaca({
        "data": data,
        "schema": schema,
        "options": options,
        "postRender": function(control) {
		
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

        	$("#alpaca" + number).remove();

			if (skipwf) {
	        	$("#alpaca" + number + "-controlfield-helper")
        		.prepend('<div><strong>No workspace files exist ...</strong></div>')

			} else {

		
			
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
								 $("#fileID" + number).html('<input type="hidden" name="_form' + key + '" value="' + tableData[1] + '"><input type="hidden" name="isFile_' + key + '" value="1">') ;
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
		
		// handle display of advanced parameters		
        $.each(adv_array, function(number, val) {
        	// grab the field html
			var a = $('#alpaca' + number + '-item-container').html();
			
			// grab the field value
			var a_tmp = $("#alpaca" + number).val();

			// remove the adv param field from the main display
			$("#alpaca" + number + '-item-container').remove();
			
			// append the adv param field to the adv param area
			$( "#adv1" ).append( a);
			
			// set the value of the adv param field
			$("#alpaca" + number).val(a_tmp);
        });
        
		// toggle the adv param icon and display
		$('.ui-icon').click( function () {
			$("#adv1").toggle();
			$("i",this).toggleClass("ui-icon-triangle-1-e ui-icon-triangle-1-s");
		});


	 },       	
        "view": "VIEW_WEB_EDIT"
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


		

