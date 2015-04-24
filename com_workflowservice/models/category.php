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

require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_workflowservice' . DS . 'tables' . DS . 'category.php');
require_once(__DIR__ . '/abstract.php');

/**
 * Answers mdoel class for a question
 */
class WorkflowserviceModelCategory extends WorkflowserviceModelAbstract
{
	/**
	 * Open state
	 *
	 * @var integer
	 */
	const ANSWERS_STATE_OPEN   = 0;

	/**
	 * Closed state
	 *
	 * @var integer
	 */
	const ANSWERS_STATE_CLOSED = 1;

	/**
	 * Deleted
	 *
	 * @var integer
	 */
	const ANSWERS_STATE_DELETE = 2;

	/**
	 * Table class name
	 *
	 * @var string
	 */
	protected $_tbl_name = 'WorkflowserviceTableCategory';

	/**
	 * Model context
	 *
	 * @var string
	 */
	protected $_context = 'com_workflowservice.category.category';

	
	/**
	 * Flag for if authorization checks have been run
	 *
	 * @var boolean
	 */
	private $_authorized = false;

	/**
	 * URL for this entry
	 *
	 * @var string
	 */
	private $_base = 'index.php?option=com_workflowservice';

	/**
	 * Returns a reference to a question model
	 *
	 * This method must be invoked as:
	 *     $offering = AnswersModelQuestion::getInstance($id);
	 *
	 * @param   integer $oid Question ID
	 * @return  object  AnswersModelQuestion
	 */
	static function &getInstance($oid=null)
	{ 
		static $instances;

		if (!isset($instances))
		{
			$instances = array();
		}

		if (!isset($instances[$oid]))
		{
			$instances[$oid] = new WorkflowserviceModelCategory($oid);
		}

		return $instances[$oid];
	}

	/**
	 * Is the question closed?
	 *
	 * @return  boolean
	 */
	public function isClosed()
	{
		if ($this->get('state') == static::ANSWERS_STATE_CLOSED)
		{
			return true;
		}
		return false;
	}

	/**
	 * Is the question open?
	 *
	 * @return  boolean
	 */
	public function isOpen()
	{
		if ($this->get('state') == static::ANSWERS_STATE_OPEN)
		{
			return true;
		}
		return false;
	}

	/**
	 * Get the state of the entry as either text or numerical value
	 *
	 * @param   string $as Format to return state in [text, number]
	 * @return  mixed  String or Integer
	 */
	public function state($as='text')
	{
		$as = strtolower($as);

		if ($as == 'text')
		{
			switch ($this->get('state'))
			{
				case 1:
					return 'closed';
				break;
				case 0:
				default:
					return 'open';
				break;
			}
		}
		else
		{
			return $this->get('state');
		}
	}

	/**
	 * Generate and return various links to the entry
	 * Link will vary depending upon action desired, such as edit, delete, etc.
	 *
	 * @param   string $type The type of link to return
	 * @return  string
	 */
	public function link($type='')
	{
		$link = $this->_base;

		// If it doesn't exist or isn't published
		switch (strtolower($type))
		{
			case 'edit':
				$link .= '&task=edit&id=' . $this->get('id');
			break;

			case 'delete':
				$link .= '&task=delete&id=' . $this->get('id');
			break;

			default:
				$link .= '&task=question&id=' . $this->get('id');
			break;
		}

		return $link;
	}

	/**
	 * Get the content of the record.
	 * Optional argument to determine how content should be handled
	 *
	 * parsed - performs parsing on content (i.e., converting wiki markup to HTML)
	 * clean  - parses content and then strips tags
	 * raw    - as is, no parsing
	 *
	 * @param   string  $as      Format to return content in [parsed, clean, raw]
	 * @param   integer $shorten Number of characters to shorten text to
	 * @return  string
	 */
	public function content($as='parsed', $shorten=0)
	{
		$as = strtolower($as);
		$options = array();

		switch ($as)
		{
			case 'parsed':
				$content = $this->get('category.parsed', null);

				if ($content === null)
				{
					$config = array(
						'option'   => 'com_workflowservice',
						'scope'    => 'category',
						'pagename' => $this->get('id'),
						'pageid'   => 0,
						'filepath' => '',
						'domain'   => ''
					);

					$content = str_replace(array('\"', "\'"), array('"', "'"), (string) $this->get('category', ''));
					$this->importPlugin('content')->trigger('onContentPrepare', array(
						$this->_context,
						&$this,
						&$config
					));

					$this->set('category.parsed', (string) $this->get('category', ''));
					$this->set('category', $content);

					return $this->content($as, $shorten);
				}

				$options['html'] = true;
			break;

			case 'clean':
				$content = html_entity_decode(strip_tags($this->content('parsed')), ENT_COMPAT, 'UTF-8');
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;

			case 'raw':
			default:
				$content = str_replace(array('\"', "\'"), array('"', "'"), $this->get('question'));
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;
		}

		if ($shorten)
		{
			$content = \Hubzero\Utility\String::truncate($content, $shorten, $options);
		}

		return $content;
	}

	/**
	 * Get the subject in various formats
	 *
	 * @param   string  $as      Format to return state in [text, number]
	 * @param   integer $shorten Number of characters to shorten text to
	 * @return  string
	 */
	public function category($as='parsed', $shorten=0)
	{
		$as = strtolower($as);
		$options = array();

		switch ($as)
		{
			case 'parsed':
				$content = $this->get('category.parsed', null);

				if ($content === null)
				{
					$config = array(
						'option'   => 'com_workflowservice',
						'scope'    => 'category',
						'pagename' => $this->get('id'),
						'pageid'   => 0,
						'filepath' => '',
						'domain'   => ''
					);

					$content = (string) $this->get('category', '');
					$this->importPlugin('content')->trigger('onContentPrepare', array(
						'com_workflowservice.category.category',
						&$this,
						&$config
					));

					$this->set('category.parsed', (string) $this->get('category', ''));
					$this->set('category', $content);

					return $this->category($as, $shorten);
				}

				$options['html'] = true;
			break;

			case 'clean':
				$content = html_entity_decode(strip_tags($this->category('parsed')), ENT_COMPAT, 'UTF-8');
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;

			case 'raw':
			default:
				$content = $this->get('category');
				$content = preg_replace('/^(<!-- \{FORMAT:.*\} -->)/i', '', $content);
			break;
		}

		if ($shorten)
		{
			$content = \Hubzero\Utility\String::truncate($content, $shorten, $options);
		}

		return $content;
	}

	/**
	 * Delete the record and all associated data
	 *
	 * @return  boolean False if error, True on success
	 */
	public function delete()
	{
		// Can't delete what doesn't exist
		if (!$this->exists())
		{
			return true;
		}

		// Attempt to delete the record
		return parent::delete();
	}
		
}