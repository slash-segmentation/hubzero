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
defined('_JEXEC') or die('Restricted access');

/**
 * Usage helper class
 */
class CwsHelper
{
	/**
	 * Return a usage database object
	 * 
	 * @return     mixed
	 */
	public function makeRequest($params) {

		if(!isset($params['access'])) die("api end-point not provided");
		
		$this->_access = $params['access'];		
		if(isset($params['method'])) { $this->_method = $params['method']; } else { $this->_method = "GET"; }
		if(isset($params['data'])) { $this->_data = $params['data']; } else { $this->_data = ""; }
		if(isset($params['url'])) { $this->_api = $params['url']; } else { $this->_api = API_DEFAULT; }
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->_api.$this->_access);
		
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		switch ($this->_method) {
   
	    	case "POST":
	        	curl_setopt($ch, CURLOPT_POST, 1);
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_data);
	        	break;
	    	case "PUT":
	        	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_data);
	        	break;
	        case "DELETE":
	        	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
	        	curl_setopt($ch, CURLOPT_POSTFIELDS, $this->_data);
	        	break;
	    	default: // get
	        	curl_setopt($ch, CURLOPT_HTTPGET, 1);
	        	break;	        
		}
		
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		$res = curl_exec($ch);
		curl_close($ch);
		return $res;
		
	}	
}
