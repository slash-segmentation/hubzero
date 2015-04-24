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
	<header id="content-header">
		<h2><?php echo $this->title; ?></h2>
	</header><!-- / #content-header -->
	
	<section class="main section">

<style>
#lefto { float: left; padding-left: 0px;}
#righto { float: left; padding-left: 10px; height: 600px; }
#blocked { padding-left: 0px; }
.cats, .wfNames { padding-bottom: 10px; font-weight: bold;}
ul, li {list-style: none outside none; }
</style>

<?php
	$mapped_categories = $this->mapped_categories;
	
	$all_descriptions = '';
	echo "<div id='blocked'>\n";
	echo "<p>Here are the available workflows:</p>\n";
	echo "<div id='lefto'><aside class='aside'><ul class='menu'>\n";
	
	if ($this->workflows) {
		// put all workflow names, version into an assoc array for sorting
		foreach ($this->workflows as $wf) {
			$workflows[$wf->name][$wf->version] = $wf->id;
		}	

		foreach (array_keys($mapped_categories) as $cats) {
			// only show Hidden categories if "show_hidden_categories" flag is true
			if (($cats !== 'Hidden') || ($this->show_hidden_categories)) {
				echo "<li class='cats'>$cats\n";
				echo "<ul>\n";
			
				foreach (array_keys($mapped_categories[$cats]) as $categorized_wf) {
					// sort each workflow by version number
					foreach (array_keys($workflows) as $wf_name) {
						// sort by array keys
						ksort($workflows[$wf_name]);

						// loop through workflow list and only show the workflow with the max version number
						foreach ($this->workflows as $wf) {
							if (($wf->name == $categorized_wf) && ($wf->name == $wf_name) && ($wf->version == max(array_keys($workflows[$wf_name])))) {
								if ($this->allow_processing)
									echo '<li class="wfNames" id="' . $wf->id . '"><a href="workflowservice/launch/' . $wf->id . '-' . str_replace(" " , "", urlencode($wf->name)) . '">' . $wf->name . " (ver " . $wf->version . ")</a></li>\n";
								else
									echo '<li class="wfNames" id="' . $wf->id . '"><a name="#">' . $wf->name . " (ver " . $wf->version . ")</a></li>\n";

								$all_descriptions .= '<div id="description_' . $wf->id . '" style="display: none">' . nl2br($wf->description) . '</div>' . "\n";
							}
						}
					}
				}
				echo "</ul>\n";
				echo "</li>\n";
			}	
		}	
	}
	echo "</div></aside></div>\n";
	echo "<div id='righto'></div>" . $all_descriptions ."\n";
	echo "</style>\n";
?>

<script type="text/javascript">
$( ".wfNames" ).hover(function() {
	var coolText = $("#description_" +this.id).html();
	$('#righto').html(coolText);
}, function() {
	$('#righto').html("");

});
</script>
