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
	<h1><?php		echo "<p><strong>Hello " . $this->name . " (" . $this->username . ")</strong>,</p>\n"; ?>
	<h2><?php echo $this->title; ?></h2>
</div><!-- / #content-header -->

<?php } ?>

<style>
.smallertext { font-size: .8em; width: 800px; }
</style>

<?php
	echo "<div>\n";
	echo "<p>Here are the available workflows:</p>\n";
	if ($this->workflows) {
		foreach ($this->workflows as $wf) {
			echo '<p><a href="cws/launch/' . $wf->id . '-' . str_replace(" " , "", $wf->name) . '">' . $wf->name . "</a><br />
			 <div class='smallertext'>(" . $wf->description . ")</div></p>\n";
		}
	}
	echo "</div>\n";

?>