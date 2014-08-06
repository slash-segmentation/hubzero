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
	<h2>Workspace Files</h2>
</div><!-- / #content-header -->

<?php }

	echo "<table border='1' cellpadding='1' cellspacing='0'>\n";
	echo "<thead>\n";
	echo "<tr><th>Name</th><th>ID</th><th>Type</th><th>Size</th><th>description</th><th>Created</th></tr>\n";
	echo "</thead>\n";
	foreach ($this->files as $file) {
		echo "<tr>\n";
		echo "<td><a href='downloadWorkspaceFile/" . $file->id . "' target='_blank'>$file->name</a></td>";
		echo "<td>$file->id</td>";
		echo "<td>$file->type</td>";
		echo "<td>$file->size</td>";
		echo "<td>$file->description</td>";
		echo "<td>" . gmdate('Y-m-d H:i:s', ($file->createDate/1000)) . "</td>"; // output = 2012-08-15 00:00:00
		echo "</tr>\n";
	}
	echo "</table>\n";
?>
