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
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

JHTML::_('behavior.modal'); 

$unique_array = array_unique($this->json);

?>
<style>
#mytoolbar-box {
    background: none repeat scroll 0 0 #607581;
    border-color: #607581;
    color: white;
}
</style>

<div id="mytoolbar-box" class="toolbar-box dark">
<div class="pagetitle icon-48-answers">
<div style="padding: 0 30px ">
	<h2>Workflow Selection</h2>
</div>	
</div>
</div>
<div style="padding: 30px ">
<p><strong>Please select the workflows to include in the " " category</strong></p>

<?php

echo "<form id='myForm'>";
echo "<input type='hidden' name='hideme' id='hideme' value='hidemenow' />";

if (isset($this->workflows->workflows))
	$exploded = array_map('trim', explode("\n", $this->workflows->workflows));
else
	$exploded = array();	
foreach ($unique_array as $ua) {
	if (in_array($ua, $exploded))
		$checked = ' checked';
	else
		$checked = '';

	echo '<input type="checkbox" name="checked[workflow]" value="' . $ua . '" ' . "$checked /> " . $ua . "<br />\n";
}	
echo "</form>";
?>
<script type="text/javascript">
var doggg = window.parent.$('#field-category').val();

	function closeFancyBox(){
		var $inputs = $('#myForm :input:checked');

		var passed = '';
		$inputs.each(function() {
			passed += "\n" + $(this).val();
		});
	
		if (passed != '') {
			window.parent.$('#category_workflows').val(passed.trim());
		}

		window.parent.$.fancybox.close();
	}
</script>

<button type="button" onclick="closeFancyBox();">Save workflows to category</button>
<button type="button" onclick="window.parent.$.fancybox.close();">Cancel</button>

</div>