<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

?>

<style>
.form-element label {
    display: block;
    width: 240px;
    font-weight: bold;
    float: left;
}
</style>

	<header id="content-header">
		<h2>Job Details</h2>
	</header><!-- / #content-header -->
	
	<section class="main section">

<div class="form-element">
  <label for="name">Name</label><?php echo $this->task->name; ?><br />
  <label for="id">Job ID</label><?php echo $this->task->id; ?><br />
  <label for="id">Workflow</label><?php echo $this->workflow_name; ?><br />
  <label for="owner">Owner</label><?php echo $this->task->owner; ?><br />
  <label for="status">Status</label><?php echo $this->task->status; ?><br />
  
  <label for="createDate">Create Date</label><?php echo UTCtoLocal($this->task->createDate); ?><br />
  <label for="submitDate">Submit Date</label><?php echo UTCtoLocal($this->task->submitDate); ?><br />
  <label for="finishDate">Finish Date</label><?php echo UTCtoLocal($this->task->finishDate); ?><br />

  <label for="estimatedCpuInSeconds">Estimated CPU (s)</label><?php echo $this->task->estimatedCpuInSeconds; ?><br />
  <label for="estimatedRunTime">Estimated run time</label><?php echo $this->task->estimatedRunTime; ?><br />
  <label for="hasJobBeenSubmittedToScheduler">Job submitted to scheduler?</label><?php echo $this->task->hasJobBeenSubmittedToScheduler ? 'yes' : 'no'; ?><br />
  <label for="downloadURL">Download URL</label><?php echo $this->task->downloadURL; ?><br />
  <label for="summaryOfErrors">Summary of Errors</label><?php echo $this->task->summaryOfErrors; ?><br />
  <label for="ErrorDetails">Error details</label><span style="display:inline-block; width: 600px;"><?php echo $this->task->detailedError; ?></span><br />
  </section>
  
  
  
  
  
  
<?php
/* Taken from launch.php template file */
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

	if (isset($this->alpaca_checkbox))
		$js_checkboxes = json_decode($this->alpaca_checkbox);


	foreach ($this->original_workflow->parameters as $wfp) {
		// deal with advanced parameters ; this should populate adv_array and alpaca_adv_ids
		if ($wfp->isAdvanced == true) {
			$wf_split = explode("?", $wfp->value);
			$tmp = $wfp->name;
			$adv_array[$js_adv_ids->$tmp] = $wfp->value;
		}
	
		$option_array[$wfp->name]['fieldClass'] = 'myClass';
	
	
	
		$wfp->name = $wfp->name;
		if ($wfp->name == 'CWS_notifyemail')
			$data_array[$wfp->name] = $this->notifyemail;
		else
			$data_array[$wfp->name] = $wfp->value;
		
		// Default schema properties will be "string"
//			$schema_array[$wfp->name] = array('type'=>textToString($wfp->type));
		$schema_array[$wfp->name]['type'] = 'string';
			
		// All fields will get a "Label", except for hidden ones	
		if ($wfp->type == 'hidden') {
			if ($wfp->name == 'CWS_jobname') {
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
				$tmp = str_replace("", "", $wfp->name);
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
		
		foreach ($this->my_parameters as $myp) {
			if (('' . $myp->name) == $wfp->name) {
				if (($wfp->type == 'checkbox') && ($myp->value == 'true'))
					$data_array[$wfp->name] = true;
				else 
					$data_array[$wfp->name] = $myp->value;
			}
		}		
	}

	// If CWS_jobname is given in the JSON, it was handled above. If not in JSON, handle here
	$cws = 'CWS_jobname';
	if (!(isset($option_array[$cws]))) {	
		$option_array[$cws] = array('label'=>'Job Name');
		$data_array[$cws] = '';
		$schema_array[$cws]['type'] = 'string';
	}	

	$cws = 'CWS_user';
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
	#adv1 { padding-left: 20px; display: none; }
	
	#boxo { }
	#lefto { position: relative; float: left; width: 600px; }
	#righto { position: relative; float: left; width: 450px; }
</style>

	<section class="parameters section">
	<h1>*** Your submitted job parameters ***</h1>
	<h2><?php echo $this->original_workflow->name; ?></h2>
		<div id="boxo">
			<div id="lefto">
				<div><?php echo nl2br($this->original_workflow->description); ?></div>

				<div id="form"></div>
				<?php
				if (isset($this->alpaca_adv_id)) { ?>
				<div id="advanced">
					<i class="ui-icon ui-icon-triangle-1-e large"></i>
					<span class="advanced_title">Advanced Parameters</span>
			
					<div id="adv1"></div>
				</div>
				<?php } ?>
			
			</div>
			<div id="righto">
				<div class="advanced_title">Release Notes</div>
				<?php echo nl2br($this->original_workflow->releaseNotes); ?>
			</div>
		</div>
		
	</section>

<script type="text/javascript">
$(document).ready(function() {
<?php if (isset($this->alpaca_file_id)) { 
	echo 'var wf_array = ' . json_encode($wf_array) . ";\n";
	echo "var alpacafiles = {};\n";

	foreach (json_decode($this->alpaca_file_id) as $key=>$value) {
		// $key = trainedModel; $value = 6
		foreach ($this->task->parameters as $params) {
			// $params->name = trainedModel, so want $params->value, which is 5805792104022016
			if ($params->name == $key) {
				foreach ($this->input_files as $input_files) {
					// if value matches id, then we have the right workspace file
					if ($params->value == $input_files['id']) {
						echo "alpacafiles[" . $value . "] =" . "'" . json_encode($input_files) . "'\n";
					}
				}
			}
		}
	}						
	
}

	 if (isset($this->alpaca_adv_id)) { 
	echo 'var adv_array = ' . json_encode($adv_array) . ";\n";
	echo 'var alpaca_adv_ids = '. $this->alpaca_adv_id . ";\n";
	}	
	if (isset($this->alpaca_checkbox)) {
		echo 'var alpaca_cb_ids = '. $this->alpaca_checkbox . ";\n";
	} else {
		echo 'var alpaca_cb_ids = {};' . "\n";
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

		$.each(wf_array, function(number, val) {
		   	$("#alpaca" + number).remove();

			var obj = jQuery.parseJSON( alpacafiles[number] );
			var filetext = "<strong>File Information:</strong><br />\n<strong>Name</strong> => " + obj.name + "<br />\n";
			var filetext = filetext + "<strong>Description</strong> => " + obj.description + "<br />\n";
			var filetext = filetext + "<strong>ID</strong> => " + obj.id + "<br />\n";
			var filetext = filetext + "<strong>Source Job ID</strong> => <a href='/workflowservice/jobDetails/" + obj.sourceJobId + "' target='_blank'>" + obj.sourceJobId + "</a><br />\n";
			var filetext = filetext + "<strong>Owner</strong> => " + obj.owner + "<br />\n";

			$("#alpaca" + number + "-controlfield-helper")
				.prepend( "<div style='padding-left: 20px; font-size: 1.1em;' id='workflowfiles" + number + "'>" + filetext + "</div>");

		});

		// handle display of advanced parameters		
        $.each(adv_array, function(number) {
        
        	// grab the field html
			var a = $('#alpaca' + number + '-item-container').html();
			
			// grab the field value
			var a_tmp = $("#alpaca" + number).val();

			// remove the adv param field from the main display
			$("#alpaca" + number + '-item-container').remove();
			
			// append the adv param field to the adv param area
			$( "#adv1" ).append( a);

			// set the value of the adv param field
			$.each(data, function (field, fieldvalue) {
				// field will eventually be [form]examplecheckbox
				$.each(alpaca_cb_ids, function(key, value) {
					var formkey = key;
					if ((formkey == field) && (fieldvalue == true)) {
						$("#alpaca" + number + '_checkbox').prop('checked', true);
					}
				});		
				$("#alpaca" + number).val(a_tmp);					
			});
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
function formatDate($date) {
	if (strlen($date))
		return gmdate('Y-m-d H:i:s', $date/1000);
	else 
		return '';
}
?>