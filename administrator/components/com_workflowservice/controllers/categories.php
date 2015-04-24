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
 * Controller class for questions
 */
class WorkflowserviceControllerCategories extends \Hubzero\Component\AdminController
{
	/**
	 * Execute a task
	 *
	 * @return     void
	 */
	public function execute()
	{
//		$this->banking = JComponentHelper::getParams('com_members')->get('bankAccounts');

		parent::execute();
	}

	public function workflowassistantTask()
	{

define("API_DEFAULT", "https://crbsworkflow.appspot.com/");

$build_url = API_DEFAULT . "/rest/workflows?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a";
$workflows = json_decode(file_get_contents(API_DEFAULT . "/rest/workflows?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a"));
//$this->view->workflows = $workflows;
$this->view->json = array();

foreach ($workflows as $wf) {
	array_push($this->view->json, $wf->name);
}


		$cw = new WorkflowserviceTableCategory($this->database);
		
		if (isset($_GET['id']))
			$this->view->workflows = $cw->getCategoryWorkflows($_GET['id']);
		else
			$this->view->workflows = new StdClass();
		$this->view->non_hidden_workflows = $cw->getAllCategoryWorkflows();

		// Output the HTML
		$this->view->display();
	}
	
	/**
	 * List all questions
	 *
	 * @return     void
	 */
	public function displayTask()
	{
		// Get Joomla configuration
		$config = JFactory::getConfig();
		$app = JFactory::getApplication();

		// Filters
		$this->view->filters = array();
		$this->view->filters['filterby'] = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.filterby',
			'filterby',
			'all'
		);

		// Paging
		$this->view->filters['limit']    = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limit',
			'limit',
			$config->getValue('config.list_limit'),
			'int'
		);
		$this->view->filters['start']    = $app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.limitstart',
			'limitstart',
			0,
			'int'
		);

		$this->view->filters['sort'] = 'ordering';
		$this->view->filters['sort_Dir'] = 'ASC';

		// Sorting
		$this->view->filters['sortby']   = '';
		$this->view->filters['sort']     = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sort',
			'filter_order',
			'ordering'
		));
		$this->view->filters['sort_Dir'] = trim($app->getUserStateFromRequest(
			$this->_option . '.' . $this->_controller . '.sortdir',
			'filter_order_Dir',
			'ASC'
		));


		$aq = new WorkflowserviceTableCategory($this->database);

		// Get a record count
		$this->view->total = $aq->getCount($this->view->filters);

		// Get records
		$this->view->results = $aq->getResults($this->view->filters);

		// Did we get any results?
		if ($this->view->results)
		{
			foreach ($this->view->results as $key => $result)
			{
				$this->view->results[$key] = new WorkflowserviceModelCategory($result);
			}
		}

		// Initiate paging
		jimport('joomla.html.pagination');
		$this->view->pageNav = new JPagination(
			$this->view->total,
			$this->view->filters['start'],
			$this->view->filters['limit']
		);

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	
	/**
	 * Create a new question
	 *
	 * @return	void
	 */
	public function addTask()
	{
		$this->editTask();
	}

	/**
	 * Displays a question for editing
	 *
	 * @return	void
	 */
	public function editTask($row=null)
	{
		JRequest::setVar('hidemainmenu', 1);

		$this->view->setLayout('edit');

		// Incoming
		$id = JRequest::getVar('id', array(0));
		if (is_array($id))
		{
			$id = $id[0];
		}

		// Load object
		if (is_object($row))
		{
			$this->view->row = $row;
		}
		else
		{
			$this->view->row = new WorkflowserviceModelCategory($id);
		}

		// Set any errors
		if ($this->getError())
		{
			foreach ($this->getErrors() as $error)
			{
				$this->view->setError($error);
			}
		}

		// Output the HTML
		$this->view->display();
	}

	/**
	 * Save a question and fall back to edit form
	 *
	 * @return     void
	 */
	public function applyTask()
	{
		$this->saveTask(false);
	}

	/**
	 * Save a question
	 *
	 * @return     void
	 */
	public function saveTask($redirect=true)
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Incoming data
		$fields = JRequest::getVar('category', array(), 'post');

		// Initiate model
		$row = new WorkflowserviceModelCategory($fields['id']);

		if (!$row->bind($fields))
		{
			$this->addComponentMessage($row->getError(), 'error');
			$this->editTask($row);
			return;
		}

		// Store content
		if (!$row->store(true))
		{
			$this->addComponentMessage($row->getError(), 'error');
			$this->editTask($row);
			return;
		}

		if ($redirect)
		{
			// Redirect back to the full questions list
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::_('COM_WORKFLOWSERVICE_CATEGORY_SAVED')
			);
		}

		$this->editTask($row);
	}

	/**
	 * Delete one or more questions and associated data
	 *
	 * @return     void
	 */
	public function removeTask()
	{
		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		// Incoming
		$ids = JRequest::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		if (count($ids) <= 0)
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller
			);
			return;
		}

		foreach ($ids as $id)
		{
			// Load the record
			$aq = new WorkflowserviceModelCategory(intval($id));

			// Delete the question
			if (!$aq->delete())
			{
				$this->setError($aq->getError());
			}
		}

		// Redirect
		if ($this->getError())
		{
			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				implode('<br />', $this->getErrors()),
				'error'
			);
			return;
		}

		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
			JText::_('COM_WORKFLOWSERVICE_QUESTION_DELETED')
		);
	}

	/**
	 * Set one or more questions to open
	 *
	 * @return     void
	 */
	public function openTask()
	{
		$this->stateTask();
	}

	/**
	 * Set one or more questions to closed
	 *
	 * @return     void
	 */
	public function closeTask()
	{
		$this->stateTask();
	}

	/**
	 * Set the state of one or more questions
	 *
	 * @return     void
	 */
	public function stateTask()
	{
		// Check for request forgeries
		JRequest::checkToken('get') or JRequest::checkToken() or jexit('Invalid Token');

		// Incoming
		$ids = JRequest::getVar('id', array());
		$ids = (!is_array($ids) ? array($ids) : $ids);

		$publish = ($this->_task == 'close') ? 1 : 0;

		// Check for an ID
		if (count($ids) < 1)
		{
			$action = ($publish == 1) ? JText::_('COM_WORKFLOWSERVICE_SET_STATE_CLOSE') : JText::_('COM_WORKFLOWSERVICE_SET_STATE_OPEN');

			$this->setRedirect(
				'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
				JText::sprintf('COM_WORKFLOWSERVICE_ERROR_SELECT_QUESTION_TO', $action),
				'error'
			);
			return;
		}

		// Load the plugins
		//JPluginHelper::importPlugin('xmessage');
		//$dispatcher = JDispatcher::getInstance();

		foreach ($ids as $id)
		{
			// Update record(s)
			$aq = new WorkflowserviceModelCategory(intval($id));
			if (!$aq->exists())
			{
				continue;
			}
			$aq->set('state', $publish);

			if (!$aq->store())
			{
				JError::raiseError(500, $aq->getError());
				return;
			}
		}

		// set message
		if ($publish == 1)
		{
			$message = JText::sprintf('COM_WORKFLOWSERVICE_QUESTIONS_CLOSED', count($ids));
		}
		else if ($publish == 0)
		{
			$message = JText::sprintf('COM_WORKFLOWSERVICE_QUESTIONS_OPENED', count($ids));
		}

		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller,
			$message
		);
	}

	/**
	 * Cancel a task and redirect to default view
	 *
	 * @return     void
	 */
	public function cancel()
	{
		$this->setRedirect(
			'index.php?option=' . $this->_option . '&controller=' . $this->_controller
		);
	}
}

 function getCategoryWorkflowsTask() {

		$this->view->filters['filterby'] = '';
		$this->view->filters['sortby'] = '';
		$this->view->filters['category'] = 'Data Transfer';

		$aq = new WorkflowserviceTableCategory($this->database);

		// Get records
		if ($res = $aq->getResults($this->view->filters))
			return $res[0]->workflows;
//		}	
	
	}
