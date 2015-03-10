<?php
/**
 * HUBzero CMS
 *
 * Copyright 2005-2013 Purdue University. All rights reserved.
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
 * @copyright Copyright 2005-2013 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Must be logged in to access forum. If not, redirect to login
$juser = JFactory::getUser();
if ((empty($juser->username))) {
	$redirectUrl = urlencode(base64_encode($_SERVER['REQUEST_URI']));
	$redirectUrl = '&return='.$redirectUrl;
	$joomlaLoginUrl = 'index.php?option=com_users&view=login';
	$finalUrl = $joomlaLoginUrl . $redirectUrl;

	echo "<script type='text/javascript'>alert('You must login to access the forum. Redirecting to login ...');";
	echo "window.location = '" . $finalUrl . "'";
	echo "</script>\n";
	exit;
}

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_COMPONENT_SITE . DS . 'models' . DS . 'forum.php');

$controllerName = JRequest::getCmd('controller', JRequest::getCmd('view', 'sections'));
if (!file_exists(JPATH_COMPONENT_SITE . DS . 'controllers' . DS . $controllerName . '.php'))
{
	$controllerName = 'sections';
}
require_once(JPATH_COMPONENT_SITE . DS . 'controllers' . DS . $controllerName . '.php');
$controllerName = 'ForumController' . ucfirst(strtolower($controllerName));

// Instantiate controller
$controller = new $controllerName();
$controller->execute();
$controller->redirect();
