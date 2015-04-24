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
 * @author    Alissa Nedossekina <alisa@purdue.edu>
 * @copyright Copyright 2005-2011 Purdue University. All rights reserved.
 * @license   http://www.gnu.org/licenses/lgpl-3.0.html LGPLv3
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

/**
 * Table class for a question
 */
class WorkflowserviceTableCategory extends JTable
{
	/**
	 * int(11) Primary key
	 *
	 * @var integer
	 */
	var $id         = NULL;

	/**
	 * varchar(25)
	 *
	 * @var string
	 */
	var $category    = NULL;

	/**
	 * int(1)
	 *
	 * @var integer
	 */
	var $isVisible   = NULL;

	/**
	 * datetime (0000-00-00 00:00:00)
	 *
	 * @var string
	 */
	var $created    = NULL;

	/**
	 * int(3)
	 *
	 * @var integer
	 */
	var $state      = NULL;

	/**
	 * text()
	 *
	 * @var string
	 */
	var $params      = NULL;

	/**
	 * Constructor
	 *
	 * @param      object &$db JDatabase
	 * @return     void
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__workflowservice_categories', 'id', $db);
	}

	/**
	 * Validate data
	 *
	 * @return     boolean True if data is valid
	 */
	public function check()
	{
		$this->category = trim($this->category);
		if ($this->category == '')
		{
			$this->setError(JText::_('The category cannot be blank.'));
			return false;
		}

		// Updating entry
		$this->created    = $this->created    ? $this->created    : JFactory::getDate()->toSql();

		// Code cleaner
		//$this->question = nl2br($this->question);

		return true;
	}

	/**
	 * Build a query from filters
	 *
	 * @param      array $filters Filters to build query from
	 * @return     string SQL
	 */
	public function buildQuery($filters=array())
	{
		$juser = JFactory::getUser();

		// build body of query
		$query  = "";
		$query .= "FROM $this->_tbl AS C ";

		switch ($filters['filterby'])
		{
			case 'mine':   $query .= "WHERE C.state!=2 "; $filters['mine'] = 1;       break;
			case 'all':    $query .= "WHERE C.state!=2 ";      break;
			case 'closed': $query .= "WHERE C.state=1 ";  break;
			case 'open':   $query .= "WHERE C.state=0 ";  break;
			case 'none':   $query .= "WHERE 1=2 ";        break;
			default:       $query .= "WHERE C.state!=2 "; break;
		}

		if (isset($filters['category'])) {
			$query .= " AND category = '{$filters['category']}'";
		}
		
		if (!isset($filters['count']) || !$filters['count'])
		{
			$sortdir = (isset($filters['sort_Dir'])) ? $filters['sort_Dir'] : 'DESC';
			$sortdir = $sortdir == 'DESC' ? 'DESC' : 'ASC';
			switch ($filters['sortby'])
			{
				default:
					if (isset($filters['sort']))
					{
						$filters['sort_Dir'] = (isset($filters['sort_Dir'])) ? $filters['sort_Dir'] : 'DESC';
						$query .= " ORDER BY " . $filters['sort'] . " " .  $filters['sort_Dir'];
					}
					else
					{
						$query .= " ORDER BY ordering";
					}
				break;
			}
		}

		return $query;
	}

	/**
	 * Get a record count
	 *
	 * @param      array $filters Filters to build query from
	 * @return     integer
	 */
	public function getCount($filters=array())
	{
		$query  = "SELECT COUNT(C.id) ";

		$filters['sortby'] = '';
		$filters['count'] = 1;
		$query .= $this->buildQuery($filters);

		$this->_db->setQuery($query);
		return $this->_db->loadResult();
	}

	/**
	 * Get records
	 *
	 * @param      array $filters Filters to build query from
	 * @return     array
	 */
	public function getResults($filters=array())
	{

		$query  = "SELECT C.*";
		$query .= $this->buildQuery($filters);
		$query .= (isset($filters['limit']) && $filters['limit'] > 0) ? " LIMIT " . $filters['start'] . ", " . $filters['limit'] : "";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get array of categories and workflows
	 *
	 * @param      string $category Category to show workflows
	 * @return     string obj->workflows ... separated by "\n"
	 */
	public function buildCategoryWorkflowArray()
	{
		$query  = "SELECT category, workflows FROM `#__workflowservice_categories` ";
		$query  .= "WHERE state = 0 ";
		$query .= "ORDER BY ordering ASC";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

	/**
	 * Get workflows within a category
	 *
	 * @param      string $category Category to show workflows
	 * @return     string obj->workflows ... separated by "\n"
	 */
	public function getCategoryWorkflows($id)
	{
		$query  = "SELECT workflows FROM `#__workflowservice_categories` ";
		$query .= "WHERE id = " . $id ;

		$this->_db->setQuery($query);
		return $this->_db->loadObject();
	}

	/**
	 * Get workflows within a category
	 *
	 * @param      string $category Category to show workflows
	 * @return     string obj->workflows ... separated by "\n"
	 */
	public function getAllCategoryWorkflows()
	{
		$query  = "SELECT workflows FROM `#__workflowservice_categories` ";
		$query .= "WHERE category <> 'Hidden'";

		$this->_db->setQuery($query);
		return $this->_db->loadObjectList();
	}

}

