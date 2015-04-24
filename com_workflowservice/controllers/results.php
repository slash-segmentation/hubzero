<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

// Must be logged in to access workflow service. If not, redirect to login
// updated to allow 'count' and 'status' calls
$juser = JFactory::getUser();
if ((empty($juser->username)) && (!(in_array($var['task'], array('count', 'status'))))) {
	$redirectUrl = urlencode(base64_encode($_SERVER['REQUEST_URI']));
	$redirectUrl = '&return='.$redirectUrl;
	$joomlaLoginUrl = 'index.php?option=com_users&view=login';
	$finalUrl = $joomlaLoginUrl . $redirectUrl;

	echo "<script type='text/javascript'>alert('You must login to access the workflow service. Redirecting to login ...');";
	echo "window.location = '" . $finalUrl . "'";
	echo "</script>\n";
	exit;
}

$params = &JComponentHelper::getParams( 'com_workflowservice' );
define("API_DEFAULT", $params->get('restURL'));
define("USERPASS", $params->get('authentication'));
define("LOGGED_IN_AS", "mikechiu");

//Set to location of Data Tables css and js files	
define("DATA_TABLES_CSS","/media/DataTables-1.10.1/media/css/jquery.dataTables.css");
define("DATA_TABLES_JS","/media/DataTables-1.10.1/media/js/jquery.dataTables.js");

require_once(JPATH_ROOT . DS . 'administrator' . DS . 'components' . DS . 'com_workflowservice' . DS . 'tables' . DS . 'category.php');
require_once(JPATH_ROOT . DS . 'components' . DS . 'com_workflowservice' . DS . 'helpers' . DS . 'helper.php');

$document = JFactory::getDocument();
$document->addStyleSheet( "/media/alpaca/css/alpaca.min.css" );
$document->addStyleSheet( "/media/alpaca/css/alpaca-core.css" );
$document->addStyleSheet( "/media/alpaca/css/alpaca-jqueryui.css" );
$document->addScript( "/media/alpaca/js/alpaca-full.min.js" );

JLog::addLogger(array('text_file' => 'debug.workflowservice.log'));


/**
 * Usage controller class for results
 */
class WorkflowserviceControllerResults extends \Hubzero\Component\SiteController
//class WorkflowserviceControllerResults extends Hubzero_Controller

{

	/**
	 * Execute a task
	 * 
	 * @return     void
	 */
	public function execute()
	{
	
		$this->registerTask('__default', 'default');

		parent::execute();
	}

	/**
	 * Display usage data
	 * 
	 * @return     void
	 */
	public function defaultTask()
	{
		$juser = JFactory::getUser();
		$this->view->name = $juser->name;
		$this->view->username = $juser->username;
	
		// Push some scripts and styles to the tmeplate
		$this->_getStyles();
		$this->_getScripts();

		// Build the page title
		$this->view->title = 'CRBS Workflow Service';

		// Set the page title
		$document =& JFactory::getDocument();
		$document->setTitle($this->view->title);

		$mk = new WorkflowserviceHelper;
		$params = array("access"=>'rest/workflows?owner=' . $juser->username, 
						"method"=>"GET", 
						"data"=>"", 
						"url"=>API_DEFAULT,
						"userpass"=>USERPASS);
		$workflows = json_decode($mk->makeRequest($params));

		$this->view->workflows = $workflows;

		$cw = new WorkflowserviceTableCategory($this->database);
		for ($i=0; $i<sizeof($cw->buildCategoryWorkflowArray()); $i++) {
			$row = $cw->buildCategoryWorkflowArray()[$i];
			foreach (explode("\n", $row->workflows) as $wf) {
				$category_mapping[$row->category][trim($wf)] = 1;
			}	
		}
		$this->view->mapped_categories = $category_mapping;

		// allow viewing of hidden category for some users 
		$params = &JComponentHelper::getParams( 'com_workflowservice' );
		if (in_array($juser->username, array_map('trim', explode(',', $params->get('see_hidden')))))
			$this->view->show_hidden_categories = true;
		else
			$this->view->show_hidden_categories = false;

		$params = &JComponentHelper::getParams( 'com_workflowservice' );
		$this->view->allow_processing = $params->get('allow_processing');

		// Output HTML
		if ($this->getError()) {
			foreach ($this->getErrors() as $error) {
				$this->view->setError($error);
			}
		}
		$this->view->display();
	}
	
	public function launchTask() {
		$params = &JComponentHelper::getParams( 'com_workflowservice' );
		if (!($params->get('allow_processing'))) {
	
			$document =& JFactory::getDocument();
			$document->setTitle('CRBS Workflow Service Offline');
			$this->view->setLayout('offline');
			
		} else {

			// get URI data
			//  Array ( [option] => workflowservice [Itemid] => [task] => launch [period] => 4-wf4 )
			$router =& JSite::getRouter();
			$var = $router->getVars();

			// If user tries to load "launch" page without a workflow, send them to workflow listing
			if (!(isset($var['period']))) {
				echo "<script type='text/javascript'>alert('Invalid workflow. Redirecting to Workflow list ...');";
				$redirectUrl = "/workflowservice";
				echo "window.location = '" . $redirectUrl . "'";
				echo "</script>\n";
				exit;
			}
	
			// get JSON for workflow
			$mk = new WorkflowserviceHelper;
			$seg = explode('-', $var['period']);
			$params = array("access"=>'rest/workflows/' . $seg[0] . '?owner=' . $juser->username, 
							"method"=>"GET", 
							"data"=>"", 
							"url"=>API_DEFAULT,
							"userpass"=>USERPASS);
			$workflow = json_decode($mk->makeRequest($params));
	//		$wf2 = file_get_contents("mega_advanced.json");
	//		$workflow = json_decode($wf2);

			// not sure why alpaca indexing starts at 2
			$counter = 3;
			$af_array = array(); // alpaca file array
			$cb_array = array(); // alpaca checkbox array
			$adv_array = array(); // advanced parameter array
			$req_file_array = array(); // required file parameter array

			$schema_array = array(); // for schema properties
			$option_array = array(); // for option fields
			$data_array = array(); // for data

			$juser = JFactory::getUser();

			// file, checkbox and adv parameters need special postRender processing
			foreach ($workflow->parameters as $wf) {
				if ($wf->type == 'file') {
					$af_array[$wf->name] = $counter;
				} elseif ($wf->type == 'checkbox') {
					$cb_array[$wf->name] = $counter;
				}

				if ($wf->isAdvanced == true) {
					$adv_array[$wf->name] = $counter;
				}

				// set default data values, notifyemail = logged in user's email
				if ($wf->name == 'CWS_notifyemail')
					$data_array[$wf->name] = $juser->email;
				else
					$data_array[$wf->name] = $wf->value;

				// other defaults ...
				$schema_array[$wf->name]['type'] = 'string';
				$option_array[$wf->name]['fieldClass'] = 'myClass';

				// All fields will get a "Label", except for hidden ones	
				if ($wf->type == 'hidden') {
					// give the jobname a proper names
					if ($wf->name == 'CWS_jobname') {
						$option_array[$wf->name] = array('label'=>'Job Name');
						$data_array[$wf->name] = $wf->value;
					} else 
						$option_array[$wf->name]['type'] = 'hidden';
				} else	
					$option_array[$wf->name] = array('label'=>$wf->displayName);
			
				// help text
				$option_array[$wf->name]['helper'] = $wf->help;

				// validation data
				switch ($wf->type) {
					case "textarea":
							$option_array[$wf->name]['type'] = 'textarea';
							$option_array[$wf->name]['rows'] = $wf->rows;
							$option_array[$wf->name]['cols'] = $wf->columns;
							$data_array[$wf->name] = $wf->value;
						break;
				
					case "file":
						$schema_array[$wf->name]['type'] = 'string';
					
						$wf_split = explode("?", $wf->value);
						$wf_split[1] = preg_replace("/owner=\w*&/", "owner=" . $juser->username . "&", $wf_split[1]);
						$data_array[$wf->name] = '';
					
						/* will manually test for validation instead of using alpaca */
						if ($wf->isRequired) {
	//						$schema_array[$wf->name]['required'] = 'true';
							$req_file_array[$wf->name] = $counter;
						}	 

						if (is_null($wf->allowedWorkspaceFileTypes)) {
							$wf_array[$counter] = $wf_split[1];
						} else {
							$wf_array[$counter] = $wf_split[1] . "&type=" . urlencode($wf->allowedWorkspaceFileTypes);
						}
					
						if ($wf->allowFailedWorkspaceFile) {
	//						$wf_array[$counter] .= "&isfailed=true";
						} else {
							$wf_array[$counter] .= "&isfailed=false";
						}	
						break;

					case "text":
						if ($wf->validationType == 'digits')
							$option_array[$wf->name]['type'] = 'integer';
						elseif ($wf->validationType == 'number')
							$option_array[$wf->name]['type'] = 'number';
						elseif (($wf->validationType == 'email') && ($wf->isRequired)) {
							$option_array[$wf->name]['type'] = 'email';
							$option_array[$wf->name]['allowOptionalEmpty'] = true;
							$schema_array[$wf->name]['format'] = 'email';
						}	
					
						if ($option_array[$wf->name]['type'] !== 'string') {	
							$option_array[$wf->name]['validate'] = true;
							$schema_array[$wf->name]['minimum'] = $wf->minValue;
							$schema_array[$wf->name]['maximum'] = $wf->maxValue;
							$schema_array[$wf->name]['pattern'] = $wf->validationRegex;
						}	 
						break;
				
					case "dropdown":
						$option_array[$wf->name]['type'] = 'select';
						$option_array[$wf->name]['dataSource'] = $wf->valueMap;
						$option_array[$wf->name]['removeDefaultNone'] = true;
						$data_array[$wf->name] = $wf->selected;
						break;
				
					case "checkbox":
						$option_array[$wf->name]['rightLabel'] = $wf->displayName;
						$option_array[$wf->name]['type'] = 'checkbox';
						unset($option_array[$wf->name]['label']);
	
						$counter++;
						$bracketed = "isCheckbox_";
						$bracketed .= $wf->name;
						$schema_array[$bracketed]['type'] = 'string';
						$option_array[$bracketed]['type'] = 'hidden';
	//					$data_array[$bracketed]['value'] = $counter;
						break;
				}			
				$counter++;
			}

			// If CWS_jobname is given in the JSON, it was handled above. If not in JSON, handle here
			$cws = 'CWS_jobname';
			if (!(isset($option_array[$cws]))) {	
				$option_array[$cws] = array('label'=>'Job Name');
				$data_array[$cws] = '';
				$schema_array[$cws]['type'] = 'string';
			}	

			$cws = '[form]CWS_user';
			if (!(isset($option_array[$cws]))) {	
				$data_array[$cws] = $this->owner;
			}	

			/* override owner = user in the CHM */
			 $data_array[$cws] = $this->owner;

			/* Need to pass the workflow ID , so create it Alapaca style */
			$data_array['workflowID'] = $workflow->id;
			$option_array['workflowID'] = 'hidden';
			$schema_array['workflowID'] = 'string';

			$o_schema->type = 'object';
			$o_schema->properties = $schema_array;
			$o_options->fields = $option_array;

			$this->view->workflow_name = $workflow->name;
			$this->view->workflow_description = $workflow->description;
			$this->view->release_notes = $workflow->releaseNotes;
		
			$this->view->alpaca_checkbox = json_encode($cb_array);
			$this->view->alpaca_file_id = json_encode($af_array);
			$this->view->alpaca_adv_id = json_encode($adv_array);
			$this->view->alpaca_wf_array = json_encode($wf_array);
			$this->view->alpaca_req_file_array = json_encode($req_file_array);

			$this->view->alpaca_data = json_encode($data_array);
			$this->view->alpaca_schema = json_encode($o_schema);
		
			$str = ltrim (json_encode($o_options), '{');
			$this->view->alpaca_options = substr($str, 0, -1) . "\n";

			$document = JFactory::getDocument();
			$document->addStyleSheet( DATA_TABLES_CSS );
			$document->addScript( DATA_TABLES_JS );

		}
		// Output the HTML  
		$this->view->display();
	}
			
	public function processJSONsuccessTask() {
		ini_set("log_errors", 1);
		ini_set("error_log", "/tmp/log_2x_errors.log");
		error_log( " - logging doublestops " . microtime(true)  );

		echo '{ "status": "success", "reason": "I said so" }';
		exit;
	}

	public function processJSONTask() {
		$params = &JComponentHelper::getParams( 'com_workflowservice' );
		if (!($params->get('allow_processing'))) {
			echo '{ "status": "error", "reason": "CRBS Workflow service processing is offline."}';
			exit;
		}		
	
		// If user tries to load "process" page with accessing it thru a form submission, send them to workflow listing
		if (!(isset($_POST['workflowID']))) {
			echo "<script type='text/javascript'>alert('Invalid form submission. Redirecting to Workflow list ...');";
			$redirectUrl = "/workflowservice";
			echo "window.location = '" . $redirectUrl . "'";
			echo "</script>\n";
			exit;
		}

		$this->view->json->id = null;
		$this->view->json->name = $_POST['CWS_jobname'];
		
		$user = JFactory::getUser();
		$this->view->json->owner = $user->username;
		
		$form_array = array();

		// For willy test job, I removed the following file block. If the form has a file, is this needed?
//		array_push($form_array, array('name' => 'examplefile', 'value'=> 'jasdfasd'));

		// send checkbox field data as either true/false rather than the default on/nothing
		foreach (array_keys($_POST) as $posted) {
			if (preg_match("/isCheckbox_(.*)/", $posted, $matches)) {
				if (isset($_POST[$matches[1]])) {
					$_POST[$matches[1]] = 'true';
				} else {
					$_POST[$matches[1]] = 'false';
				}
				unset($_POST[$posted]);
			}
		}
		foreach (array_keys($_POST) as $posted) {
			if (isset($_POST['isFile_' . $posted])) {
				array_push($form_array, array('name' => $posted, 'value' => $_POST[$posted], 'isWorkspaceId' => 1));
			} elseif (!(  ($posted == 'workflowID') || ($posted == 'submit') || ($posted == 'option') || (preg_match("/isFile/", $posted) || (preg_match("/files(.*)_length/", $posted))))) {
				array_push($form_array, array('name' => $posted, 'value' => $_POST[$posted]));
			}	
		}

//		$this->view->json->parameters = array(array('name' => 'param1', 'value'=> 'jasdfasd'), array('name'=>'asdfasd', 'value'=> 'asdfasd'));
		$this->view->json->parameters = $form_array;
		$this->view->json->workflow->id = $_POST['workflowID'];
		$this->view->json->workflow->parentWorkflow = null;

		$newjson = json_encode($this->view->json);

		$mk = new WorkflowserviceHelper;
		$params = array("access"=>'rest/jobs?runasuser=' . $user->username , 
						"method"=>"POST", 
						"data"=>$newjson, 
						"url"=>API_DEFAULT,
						"userpass"=>USERPASS,
						"header"=>array('Content-Type:application/json'));
		$results = $mk->makeRequest($params);
		$results_json = json_decode($results);
			if ($results_json->summaryOfErrors) {
			$err_string = '';
			$err_html = '';
			
			foreach (explode("\n", $results_json->summaryOfErrors) as $err) {
				$err_string .= trim($err) . "<br />";
			}		
			echo '{ "status": "error", "reason": "' . str_replace('"', '&quot;', $err_string) . '"}';
			exit;
		} elseif($results_json->id) {
			echo '{ "status": "success" }';
			exit;
		}

	}
		/*** Show list of workspace files ***/
	public function WorkspaceFilesTask() {
		$document = JFactory::getDocument();
		$document->addStyleSheet( DATA_TABLES_CSS );
		$document->addScript( DATA_TABLES_JS );

		/* Cut out a lot of code here since the jobs page relies on JobsJSON for its data */
		
        // Output the HTML  
        $this->view->display();		

	}

	/*** Generates JSON list of workspace files using pagination instructions from DataTables JS library ***/
	public function WorkspaceFilesJSONTask() {
		$juser = JFactory::getUser();

	/* these work:
	https://crbsworkflow.appspot.com:443//rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a
	https://crbsworkflow.appspot.com:443//rest/workspacefiles?userlogin=chris&usertoken=dc5902078cfa40b980229662c2e0c226
	*/

	$test = 1;

	if ($test) {
		$access_params = "rest/workspacefiles?owner=" . $juser->username . "&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a";
		if (JRequest::getVar('type')) {
			$access_params .= "&type=" . urlencode(JRequest::getVar('type'));
		}
		$access_params .= "&isfailed=" . JRequest::getVar('isfailed');

		$mk = new WorkflowserviceHelper;
		$params = array("access"=>$access_params, 
						"method"=>"GET", 
						"data"=>"", 
						"url"=>API_DEFAULT,
						"userpass"=>USERPASS);
		$files_json = $mk->makeRequest($params);
//		$files_json = file_get_contents("workspacefiles.json");

	   // Output the HTML  
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');

		$total = sizeof(json_decode($files_json));
		echo '{
	  "sEcho": 0,
	  "recordsTotal": ' . $total . ',
	  "recordsFiltered": ' . $total . ',
	  "data":  ';
		if (isset($_GET['start'])) {
			$decoded = json_decode($files_json);
			$json = array();
			for ($i=$_GET['start']; $i<($_GET['start'] + $_GET['length']); $i++) {
				$decoded[$i]->DT_RowId = $decoded[$i]->id . "_" . $decoded[$i]->name;
				$decoded[$i]->formatted_createDate = UTCtoLocal($decoded[$i]->createDate);
				array_push($json, $decoded[$i]);
			}
			echo json_encode($json);
		} else {
			$decoded = json_decode($files_json);
			$json = array();
			foreach ($decoded as $de) {
				$wf = $de->workflow;
			
				$de->formatted_createDate = UTCtoLocal($de->createDate);
				array_push($json, $de);
			}

			echo json_encode($json);
		}	
		echo "}";

		JFactory::getApplication()->close(); // or jexit();	
} else {	
	
		$router =& JSite::getRouter();
		$var = $router->getVars();
//		$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
		
		if (isset($var['owner'])) {
			$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?owner=" . $var['owner'] . "&userlogin=" . $var['userlogin'] . "&usertoken=" . $var['usertoken'] );
		} else {
			$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?userlogin=" . $var['userlogin'] . "&usertoken=" . $var['usertoken'] );
		}		
		$filesize = sizeof(json_decode($files_json));
		
	   // Output the HTML  
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');

		echo '{
	  "sEcho": 0,
	  "recordsTotal": ' . $filesize . ',
	  "recordsFiltered": ' . $filesize . ',
	  "data":  ';
	  
		  if ($filesize) {
			if (isset($_GET['start'])) {
				$decoded = json_decode($files_json);
				$json = array();
				for ($i=$_GET['start']; $i<($_GET['start'] + $_GET['length']); $i++) {
					$decoded[$i]->DT_RowId = $decoded[$i]->id . "_" . $decoded[$i]->name;
					$decoded[$i]->formatted_createDate = UTCtoLocal($decoded[$i]->createDate);

					array_push($json, $decoded[$i]);
				}
			  echo json_encode($json);
			  echo "\n}";
			} else {
				$decoded = json_decode($files_json);
				$json = array();
				foreach ($decoded as $de) {
					$wf = $de->workflow;
			
					$de->formatted_createDate = UTCtoLocal($de->createDate);
					array_push($json, $de);
				}

				echo json_encode($json);
				echo "\n}";
			}
		} else {
			echo '""' . "\n}";
		}			

		JFactory::getApplication()->close(); // or jexit();	
		}
	}
	
	public function downloadWorkspaceFileTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();
		echo "This should download a file with ID: " . $var['period'] . "\n";
	}

	public function JobsTask() {
		$document = JFactory::getDocument();
		$document->addStyleSheet( DATA_TABLES_CSS );
		$document->addScript( DATA_TABLES_JS );

		/* Cut out a lot of code here since the jobs page relies on JobsJSON for its data */
		
        // Output the HTML  
        $this->view->display();		

	}

	public function JobsJSONTask() {
		$juser = JFactory::getUser();

		$mk = new WorkflowserviceHelper;
		$params = array("access"=>"rest/jobs?owner=" . $juser->username . "&noparams=true&noworkflowparams=true", 
						"method"=>"GET", 
						"data"=>"", 
						"url"=>API_DEFAULT,
						"userpass"=>USERPASS);
		$tasks_json = $mk->makeRequest($params);
		
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');

		$total = sizeof(json_decode($tasks_json));
		echo '{
	  "sEcho": 0,
	  "recordsTotal": ' . $total . ',
	  "recordsFiltered": ' . $total . ',
	  "data":  ';
		if (isset($_GET['start'])) {
			$decoded = json_decode($tasks_json);
			$json = array();
			for ($i=$_GET['start']; $i<($_GET['start'] + $_GET['length']); $i++) {
				$decoded[$i]->DT_RowId = $decoded[$i]->id . "_" . $decoded[$i]->name;
				array_push($json, $decoded[$i]);
			}
			echo json_encode($json);
		} else {
			$decoded = json_decode($tasks_json);
			$json = array();
			foreach ($decoded as $de) {
				$wf = $de->workflow;
			
				$de->workflow_with_version = $wf->name . " (" . $wf->version . ")";
				$de->formatted_createDate = UTCtoLocal($de->createDate);
				array_push($json, $de);
			}
			echo json_encode($json);
		}	
		echo "}";

		JFactory::getApplication()->close(); // or jexit();
		
		// Output the HTML  
		echo $tasks_json;
        $this->view->display();		
	}	
	
	public function JobDetailsTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();

		// If user tries to load "jobdetails" page without passing it a job, send them to jobs listing
		if (!(isset($var['period']))) {
			echo "<script type='text/javascript'>alert('Invalid job. Redirecting to Job list ...');";
			$redirectUrl = "/workflowservice/jobs";
			echo "window.location = '" . $redirectUrl . "'";
			echo "</script>\n";
			exit;
		}
		$document = JFactory::getDocument();
		$document->addStyleSheet(DATA_TABLES_CSS );
		$document->addScript( DATA_TABLES_JS );

		$mk = new WorkflowserviceHelper;
		$params = array("access"=>"rest/jobs/{$var['period']}?owner=" . $juser->username, 
						"method"=>"GET", 
						"data"=>"", 
						"url"=>API_DEFAULT,
						"userpass"=>USERPASS);
		$task_json = $mk->makeRequest($params);

		$task = json_decode($task_json);
		$this->view->task = $task;
		$this->view->workflow_name = $task->workflow->name . " (version " . $task->workflow->version . ")";
		$this->view->my_parameters = $task->parameters;
		
		$workflow_json = file_get_contents(API_DEFAULT . "/rest/workflows/" . $task->workflow->id . "?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
		$this->view->original_workflow = json_decode($workflow_json);


		$counter = 2;
		$af_array = array(); // alpaca file array
		$adv_array = array(); // advanced parameter array
		foreach ($task->workflow->parameters as $wf) {
			if ($wf->type == 'file') {
				$af_array[$wf->name] = $counter;
				$this->view->alpaca_file_id = json_encode($af_array);
			} elseif ($wf->type == 'checkbox') {
				$cb_array[$wf->name] = $counter;
				$this->view->alpaca_checkbox = json_encode($cb_array);
			}

			if ($wf->isAdvanced == true) {
				$adv_array[$wf->name] = $counter;
				$this->view->alpaca_adv_id = json_encode($adv_array);
			}
			$counter++;
		}
		
		$afd = array();
		foreach ($task->parameters as $params) {
			if ($params->workflowParameter->type == 'file') {
				$file_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles/" . $params->value . "?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
				$tmp = json_decode($file_json, TRUE);
				array_push($afd, $tmp);
			}
		}
		$this->view->input_files = $afd;
		
        // Output the HTML  
        $this->view->display();		

	}

	public function uploadTask() {
		$uploads_dir = '/Users/mchiu/Sites/hubzero_fresh/uploads/';
		$new_file = "$uploads_dir" . $_FILES['filename']['name'];
		if (move_uploaded_file($_FILES['filename']["tmp_name"], $new_file)) {


			if (isset($_FILES['filename']['tmp_name'])) {
				$md5 = md5_file($new_file);
				$info = new SplFileInfo($new_file);

				$json = '{"createDate" : null,"description" : "text about file","blobKey" : null,"md5" : null,"deleted" : false, "dir" : false, "sourceJobId" : null, 
				"uploadURL" : null, "owner" : "bob", "id" : null, "type" : "' . $info->getExtension() . '", "path" : "' . $uploads_dir . '", "name" : "' . $_FILES['filename']['name'] . '", "size" : ' . filesize($new_file) . '}';

				$results = registerWorkspaceFile($json);
				$res_obj = json_decode($results);
print_r($results);
				// Create a cURL handle
				$ch = curl_init($res_obj->uploadURL);

				// Assign POST data
				$post = array($res_obj->id => '@'. $new_file);
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
				// Execute the handle
				$results = curl_exec($ch);
			}	
		}
	}
	
	/* 	Displays the last login date for a user 
		format: /count/time_period_in_integer_minutes 
	*/
	public function countTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();
		
		$db	=& JFactory::getDBO();
		$query = $db->getQuery(true);

		if (isset($var['period']) && (!(empty($var['period'])))) {		
			// activity_time is in minutes
			$activity_time = time() - $var['period'] * 60;

			$query->select('session_id, time');
			$query->from('#__session');
			$query->where("guest = 0 AND time>'$activity_time' ");
			$db->setQuery( $query );
			$rows = $db->loadObjectList();
	
			echo '{"status":"success","count":"' . count($db->loadResult()) . '"}';
		} else {
			echo '{"status":"error", "reason": "time period in minutes is missing"}';
		}	
		exit;
	}
	
	/* 	Displays the last login date for a user 
		format: /status/username
		
		format: /status will get last login date overall 
	*/
	public function statusTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();

		$db	=& JFactory::getDBO();
		$query = $db->getQuery(true);

		if (isset($var['period']) && (!(empty($var['period'])))) {		
			$query->select('username, lastVisitDate');
			$query->from('#__users');
			$query->where("username = '" . $var['period'] . "'");
			$db->setQuery( $query );
			$rows = $db->loadObjectList();
		} else {
			$query->select('username, lastVisitDate');
			$query->from('#__users');
			$query->order("lastVisitDate desc");
			$db->setQuery( $query );
			$rows = $db->loadObjectList();
		}
	
        if ($rows) {
			foreach ($rows as $person) {
				$person->status = 'success';
                $person->lastVisitDate = UTCtoLocal(strtotime($person->lastVisitDate . " GMT"));
				echo json_encode($person);
				exit;
			}	
		} else {
			$err_msg = 'user not found';
		}

		echo '{"status":"error", "reason": "' . $err_msg . '"}';
		exit;			
	}
	
	public function uploaderTask() {
	// Output the HTML  
        $this->view->display();	
	}

	public function makePreviewTask() {
	/* 
		I take in JSON data thru a POST ... body
		I save JSON somewhere
		I send back URL similar to /cws/preview/#id 
	*/
//	$uploads_dir = '/Users/mchiu/Sites/hubzero_fresh/uploads';
	$uploads_dir = getcwd() . "/uploads";

	$new_file = time() . "_" .	$_FILES["_formexamplefile"]["name"];
	if (move_uploaded_file($_FILES["_formexamplefile"]["tmp_name"], $uploads_dir . "/" . $new_file)) {
//		echo "http://hubzero_fresh/cws/preview/" . str_replace(".json", "", $new_file);
		echo $_SERVER['REMOTE_HOST'] . "/cws/preview" . str_replace(".json", "", $new_file);
		exit;
	}	
}

	public function previewTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();
	
		$filename = "uploads/" . $var['period'] . ".json";
		$workflow = json_decode(file_get_contents("uploads/" . $var['period'] . ".json"));
		$this->view->workflow = $workflow;

			// Output the HTML  
		$this->view->display(); 
	}    

	
	public function deletejobTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();
		
	 	$url = API_DEFAULT . '/rest/jobs/' . $var['period'] . '?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a';
	 	$json = '{"status":"Deleted"}';
	 	
//		$data = array("first_name" => "First name","last_name" => "last name","email"=>"email@gmail.com","addresses" => array ("address1" => "some address" ,"city" => "city","country" => "CA", "first_name" =>  "Mother","last_name" =>  "Lastnameson","phone" => "555-1212", "province" => "ON", "zip" => "123 ABC" ) );
//		$data_string = json_encode($data);
//		$data_string = '{"createDate" : null,"description" : "text about file","blobKey" : null,"md5" : null,"deleted" : false, "dir" : false, "sourceTaskId" : null, "uploadURL" : null, "owner" : "bob", "id" : null, "type" : "png", "path" : null, "name" : "foo.png", "size" : 123123}';


		$mk = new WorkflowserviceHelper;
		$params = array("access"=>'rest/jobs/' . $var['period'] .  $juser->username, 
						"method"=>"POST", 
						"data"=>$json, 
						"url"=>API_DEFAULT,
						"userpass"=>USERPASS,
						"header"=>array('Content-Type:application/json'));
		$results = json_decode($mk->makeRequest($params));


		print_r($results);
		exit;

	}			
	
}	

	function registerWorkspaceFile($json) {
	 	$url = API_DEFAULT . '/rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a';

		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
				   array('Content-Type:application/json')
				   );

		$results = curl_exec($ch);
		curl_close($ch);

		return $results;	

	}
	
	function UTCtoLocal($local_in_UTC) {
		if ($local_in_UTC == '') {
			return '';
		}	
			
		if (strlen($local_in_UTC) > 10)
			$local_in_UTC = floor($local_in_UTC/1000);

		$dt = new DateTime("@$local_in_UTC");  // convert UNIX timestamp to PHP DateTime
		$epoch_time = $dt->format('Y-m-d H:i:s');

		$TimeZoneNameFrom="UTC";
		$TimeZoneNameTo="America/Los_Angeles";


		return date_create($epoch_time, new DateTimeZone($TimeZoneNameFrom))
				->setTimezone(new DateTimeZone($TimeZoneNameTo))->format("Y-m-d H:i:s");
	}

	function checkWikiExistance($title) {
		$database = JFactory::getDBO();
		$query = "SELECT id FROM `#__wiki_page` WHERE title='". $title . "' AND scope='' ORDER BY state ASC LIMIT 1";
		$database->setQuery($query);
		if ($database->loadResult()) 
			return false;
		else
			return true;	
	}