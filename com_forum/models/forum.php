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

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ROOT . DS . 'components' . DS . 'com_forum' . DS . 'models' . DS . 'section.php');

/**
 * Model class for a forum
 */
class ForumModel extends ForumModelAbstract
{
	/**
	 * Container for interally cached data
	 *
	 * @var array
	 */
	private $_cache = array(
		'section'        => null,
		'sections_count' => null,
		'sections_first' => null,
		'sections'       => null,
		'posts.count'    => null,
		'posts.list'     => null,
		'last'           => null
	);

	/**
	 * Constructor
	 *
	 * @param   string  $scope    Forum scope [site, group, course]
	 * @param   integer $scope_id Forum scope ID (group ID, couse ID)
	 * @return  void
	 */
	public function __construct($scope='site', $scope_id=0)
	{
		$this->_db = JFactory::getDBO();

		$this->_tbl = new stdClass;

		$this->set('scope', $scope);
		$this->set('scope_id', $scope_id);
	}

	/**
	 * Returns a reference to a forum model
	 *
	 * @param   string  $scope    Forum scope [site, group, course]
	 * @param   integer $scope_id Forum scope ID (group ID, couse ID)
	 * @return  object ForumModel
	 */
	static function &getInstance($scope='site', $scope_id=0)
	{
		static $instances;

		if (!isset($instances))
		{
			$instances = array();
		}

		$key = $scope . '_' . $scope_id;

		if (!isset($instances[$key]))
		{
			$instances[$key] = new self($scope, $scope_id);
		}

		return $instances[$key];
	}

	/**
	 * Returns a property of the object or the default value if the property is not set.
	 *
	 * @param   string $property The name of the property
	 * @param   mixed  $default The default value
	 * @return  mixed  The value of the property
 	 */
	public function get($property, $default=null)
	{
		if (isset($this->_tbl->$property))
		{
			return $this->_tbl->$property;
		}
		return $default;
	}

	/**
	 * Modifies a property of the object, creating it if it does not already exist.
	 *
	 * @param   string $property The name of the property
	 * @param   mixed  $value The value of the property to set
	 * @return  mixed  Previous value of the property
	 */
	public function set($property, $value = null)
	{
		$previous = isset($this->_tbl->$property) ? $this->_tbl->$property : null;
		$this->_tbl->$property = $value;
		return $previous;
	}

	/**
	 * Populate the forum with defaulta section and category
	 *
	 * @return  boolean
	 */
	public function setup()
	{
		// Create a default section
		$section = new ForumModelSection(0, $this->get('scope'), $this->get('scope_id'));
		$section->bind(array(
			'title'    => JText::_('Default Section'),
			'scope'    => $this->get('scope'),
			'scope_id' => $this->get('scope_id'),
			'state'    => 1
		));
		if (!$section->store(true))
		{
			$this->setError($section->getError());
			return false;
		}

		// Create a default category
		$category = new ForumModelCategory(0);
		$category->bind(array(
			'title'       => JText::_('Discussions'),
			'description' => JText::_('Default category for all discussions in this forum.'),
			'section_id'  => $section->get('id'),
			'scope'       => $this->get('scope'),
			'scope_id'    => $this->get('scope_id'),
			'state'       => 1
		));
		if (!$category->store(true))
		{
			$this->setError($category->getError());
			return false;
		}

		$this->_cache['sections'] = new \Hubzero\Base\ItemList(array($section));

		return true;
	}

	/**
	 * Set and get a specific section
	 *
	 * @return  object
	 */
	public function section($id=null)
	{
		if (!isset($this->_cache['section'])
		 || ($id !== null && (int) $this->_cache['section']->get('id') != $id && (string) $this->_cache['section']->get('alias') != $id))
		{
			$this->_cache['section'] = null;

			if ($this->_cache['sections'] instanceof \Hubzero\Base\ItemList)
			{
				foreach ($this->_cache['sections'] as $key => $section)
				{
					if ((int) $section->get('id') == $id || (string) $section->get('alias') == $id)
					{
						$this->_cache['section'] = $section;
						break;
					}
				}
			}

			if (!$this->_cache['section'])
			{
				$this->_cache['section'] = ForumModelSection::getInstance($id, $this->get('scope'), $this->get('scope_id'));
			}
			if (!$this->_cache['section']->exists())
			{
				$this->_cache['section']->set('scope', $this->get('scope'));
				$this->_cache['section']->set('scope_id', $this->get('scope_id'));
			}
		}

		return $this->_cache['section'];
	}

	/**
	 * Get a list of sections for a forum
	 *
	 * @param      string  $rtrn    What data to return [count, list, first]
	 * @param      array   $filters Filters to apply to data fetch
	 * @param      boolean $clear   Clear cached data?
	 * @return     mixed
	 */
	public function sections($rtrn='', $filters=array(), $clear=false)
	{
		if (!isset($filters['scope']))
		{
			$filters['scope'] = (string) $this->get('scope');
		}
		if (!isset($filters['scope_id']))
		{
			$filters['scope_id'] = (int) $this->get('scope_id');
		}

		$tbl = new ForumTableSection($this->_db);

		switch (strtolower($rtrn))
		{
			case 'count':
				if (!isset($this->_cache['sections_count']) || $clear)
				{
					$this->_cache['sections_count'] = (int) $tbl->getCount($filters);
				}
				return $this->_cache['sections_count'];
			break;

			case 'first':
				if (!($this->_cache['sections_first'] instanceof ForumModelSection) || $clear)
				{
					$filters['limit'] = 1;
					$filters['start'] = 0;
					$filters['sort'] = 'created';
					$filters['sort_Dir'] = 'ASC';
					$results = $tbl->getRecords($filters);
					$res = isset($results[0]) ? $results[0] : null;

					$this->_cache['sections_first'] = new ForumModelSection($res);
				}
				return $this->_cache['sections_first'];
			break;

			case 'list':
			case 'results':
			default:
				if (!($this->_cache['sections'] instanceof \Hubzero\Base\ItemList) || $clear)
				{
					if ($results = $tbl->getRecords($filters))
					{
						foreach ($results as $key => $result)
						{
							$results[$key] = new ForumModelSection($result);
						}
					}
					else
					{
						$results = array();
					}
					$this->_cache['sections'] = new \Hubzero\Base\ItemList($results);
				}
				return $this->_cache['sections'];
			break;
		}
	}

	/**
	 * Get a list or count of posts for a forum
	 *
	 * @param   string  $rtrn    Data to return
	 * @param   array   $filters Filters to apply to the query
	 * @param   boolean $clear   Clear cached results?
	 * @return  mixed
	 */
	public function posts($rtrn='list', $filters=array(), $clear=false)
	{
		$filters['scope']    = isset($filters['scope'])    ? $filters['scope']    : $this->get('scope');
		$filters['scope_id'] = isset($filters['scope_id']) ? $filters['scope_id'] : $this->get('scope_id');
		$filters['state']    = isset($filters['state'])    ? $filters['state']    : self::APP_STATE_PUBLISHED;
		$filters['parent']   = isset($filters['parent'])   ? $filters['parent']   : -1;

		switch (strtolower($rtrn))
		{
			case 'count':
				if (!isset($this->_cache['posts.count']) || $clear)
				{
					$tbl = new ForumTablePost($this->_db);
					$this->_cache['posts.count'] = $tbl->count($filters);
				}
				return $this->_cache['posts.count'];
			break;

			case 'first':
				return $this->posts('list', $filters)->first();
			break;

			case 'list':
			case 'results':
			default:
				if (!($this->_cache['posts.list'] instanceof \Hubzero\Base\ItemList) || $clear)
				{
					$tbl = new ForumTablePost($this->_db);

					if (($results = $tbl->find($filters)))
					{
						foreach ($results as $key => $result)
						{
							$results[$key] = new ForumModelPost($result);
						}
					}
					else
					{
						$results = array();
					}

					$this->_cache['posts.list'] = new \Hubzero\Base\ItemList($results);
				}

				return $this->_cache['posts.list'];
			break;
		}
	}

	/**
	 * Check a user's authorization
	 *
	 * @param   string  $action    Action to check
	 * @param   string  $assetType Type of asset to check
	 * @param   integer $assetId   ID of item to check access on
	 * @return  boolean True if authorized, false if not
	 */
	public function access($action='view', $assetType='section', $assetId=null)
	{
		if (!$this->config()->get('access-check-done', false))
		{
			$this->config()->set('access-view-' . $assetType, true);

			if (!$juser->get('guest'))
			{
				$asset  = 'com_forum';
				if ($assetId)
				{
					$asset .= ($assetType != 'component') ? '.' . $assetType : '';
					$asset .= ($assetId) ? '.' . $assetId : '';
				}

				$at = '';
				if ($assetType != 'component')
				{
					$at .= '.' . $assetType;
				}

				// Admin
				$this->config()->set('access-admin-' . $assetType, $juser->authorise('core.admin', $asset));
				$this->config()->set('access-manage-' . $assetType, $juser->authorise('core.manage', $asset));
				// Permissions
				$this->config()->set('access-create-' . $assetType, $juser->authorise('core.create' . $at, $asset));
				$this->config()->set('access-delete-' . $assetType, $juser->authorise('core.delete' . $at, $asset));
				$this->config()->set('access-edit-' . $assetType, $juser->authorise('core.edit' . $at, $asset));
				$this->config()->set('access-edit-state-' . $assetType, $juser->authorise('core.edit.state' . $at, $asset));
				$this->config()->set('access-edit-own-' . $assetType, $juser->authorise('core.edit.own' . $at, $asset));
			}

			$this->config()->set('access-check-done', true);
		}

		return $this->config()->get('access-' . $action . '-' . $assetType);
	}

	/**
	 * Return a count for the type of data specified
	 *
	 * @param   string $what What to count
	 * @return  integer
	 */
	public function count($what='threads')
	{
		$key = 'stats.' . strtolower(trim($what));

		if (!isset($this->_cache[$key]))
		{
			$this->_cache[$key] = 0;

			switch ($key)
			{
				case 'stats.sections':
					$this->_cache[$key] = $this->sections()->total();
				break;

				case 'stats.categories':
					foreach ($this->sections() as $section)
					{
						$this->_cache[$key] += $section->categories()->total();
					}
				break;

				case 'stats.threads':
					foreach ($this->sections() as $section)
					{
						$this->_cache[$key] += $section->count('threads');
					}
				break;

				case 'stats.posts':
					foreach ($this->sections() as $section)
					{
						$this->_cache[$key] += $section->count('posts');
					}
				break;

				default:
					$this->setError(JText::sprintf('Property value of "%s" not accepted', $what));
					return 0;
				break;
			}
		}

		return $this->_cache[$key];
	}

	/**
	 * Get the most recent post made in the forum
	 *
	 * @return  object ForumModelPost
	 */
	public function lastActivity()
	{
		if (!($this->_cache['last'] instanceof ForumModelPost))
		{
			$post = new ForumTablePost($this->_db);
			if (!($last = $post->getLastActivity($this->get('scope_id'), $this->get('scope'))))
			{
				$last = 0;
			}
			$this->_cache['last'] = new ForumModelPost($last);
		}
		return $this->_cache['last'];
	}
}

