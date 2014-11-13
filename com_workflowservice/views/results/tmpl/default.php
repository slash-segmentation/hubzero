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
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<?php } ?>

<style>
#lefto { float: left; }
#righto { float: left; padding-left: 50px; height: 600px; }
#blocked { padding-left: 40px; }
.wfNames { padding-bottom: 10px; font-weight: bold;}
ul, li {list-style: none outside none; }
</style>

<?php
	$all_descriptions = '';
	echo "<div id='blocked'>\n";
	echo "<p>Here are the available workflows:</p>\n";
	echo "<div id='lefto'><aside class='aside'><ul class='menu'>\n";
	
	if ($this->workflows) {
		// put all workflow names, version into an assoc array for sorting
		foreach ($this->workflows as $wf) {
			$workflows[$wf->name][$wf->version] = $wf->id;
		}	

		// sort each workflow by version number
		foreach (array_keys($workflows) as $wf_name) {
			// sort by array keys
			ksort($workflows[$wf_name]);

			// loop through workflow list and only show the workflow with the max version number
			foreach ($this->workflows as $wf) {
				if (($wf->name == $wf_name) && ($wf->version == max(array_keys($workflows[$wf_name])))) {
					echo '<li class="wfNames" id="' . $wf->id . '"><a href="workflowservice/launch/' . $wf->id . '-' . str_replace(" " , "", urlencode($wf->name)) . '">' . $wf->name . " (version " . $wf->version . ")</a></li>\n";
					$all_descriptions .= '<div id="description_' . $wf->id . '" style="display: none">' . nl2br($wf->description) . '</div>' . "\n";
				}
			}
		}	
	}
	echo "</div></aside></div>\n";
	echo "<div id='righto'></div>" . $all_descriptions ."\n";
?>

<script type="text/javascript">
$( ".wfNames" ).hover(function() {
	var coolText = $("#description_" +this.id).html();
	$('#righto').html(coolText);
}, function() {
	$('#righto').html("");

});
</script>
