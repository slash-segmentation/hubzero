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

	foreach ($this->workflow->parameters as $wfp) {
		$wfp->name = "[form]" . $wfp->name;
		$data_array[$wfp->name] = $wfp->value;
		
		// Default schema properties will be "string"
//			$schema_array[$wfp->name] = array('type'=>textToString($wfp->type));
		$schema_array[$wfp->name]['type'] = 'string';
			
		// All fields will get a "Label", except for hidden ones	
		if ($wfp->type == 'hidden')
			$option_array[$wfp->name]['type'] = 'hidden';
		else	
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
				break;

			case "text":
				if ($wfp->validationType == 'digits')
					$option_array[$wfp->name]['type'] = 'integer';
				elseif ($wfp->validationType == 'number')
					$option_array[$wfp->name]['type'] = 'number';
					
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
	
	$o_schema->type = 'object';
	$o_schema->properties = $schema_array;
	$o_options->fields = $option_array;

if (!$this->no_html) { ?>
	<div id="content-header" class="full">
		<h2><?php echo $this->workflow->name; ?></h2>
		<div>Description: <?php echo $this->workflow->description; ?></div>
	</div><!-- / #content-header -->

<form method="post" action="/cws/process" enctype="multipart/form-data">
<input type='hidden' name='workflowID' value = '<?php echo $this->workflow->id ?>' />
<div id="form"></div>
<input type="submit">
</form>

<script type="text/javascript">
$(document).ready(function() {

<?php
	echo 'var data = ' . json_encode($data_array) . ";\n";
	echo 'var schema = ' . json_encode($o_schema) . ";\n";
	echo 'var options = ' . json_encode($o_options) . ";\n";
	echo ' var postRenderCallback = function(control) { }; ' . "\n";
?>

 $("#form").alpaca({
        "data": data,
        "schema": schema,
        "options": options,
        "postRender": postRenderCallback,
        "view": "VIEW_WEB_EDIT"
    });
});
</script>	

<?php } 

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


		
