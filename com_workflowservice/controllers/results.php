<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

define("API_DEFAULT", "https://testcrbsworkflow.appspot.com/");
define("LOGGED_IN_AS", "mikechiu");

//Set to location of Data Tables css and js files	
define("DATA_TABLES_CSS","/media/DataTables-1.10.1/media/css/jquery.dataTables.css");
define("DATA_TABLES_JS","/media/DataTables-1.10.1/media/js/jquery.dataTables.js");

JLog::addLogger(array('text_file' => 'debug.workflowservice.log'));
		
$router =& JSite::getRouter();
$var = $router->getVars();
$check_user = $var['period'];

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

	$document = JFactory::getDocument();
	$document->addStyleSheet( "/media/alpaca/css/alpaca.min.css" );
	$document->addStyleSheet( "/media/alpaca/css/alpaca-core.css" );
	$document->addStyleSheet( "/media/alpaca/css/alpaca-jqueryui.css" );
	$document->addScript( "/media/alpaca/js/alpaca-full.min.js" );

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
		
		$build_url = API_DEFAULT . "/rest/workflows?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a";
		$workflows = json_decode(file_get_contents(API_DEFAULT . "/rest/workflows?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a"));
		$this->view->workflows = $workflows;

		// Category mapping for known workflows
		$category_mapping['Data Transfer']['NCMIR Data Import'] = 1;
		$category_mapping['Data Transfer']['Import Data NCMIR'] = 1;
		$category_mapping['Data Transfer']['Export Data NCMIR'] = 1;
		$category_mapping['Workspace File Validation']['CHM training dataset'] = 1;
		$category_mapping['Workspace File Validation']['CHM image dataset'] = 1;
		$category_mapping['Automated Segmentation']['CHM'] = 1;
		$category_mapping['Automated Segmentation']['CHM Train'] = 1;
//		$category_mapping['Visualization'] = 1;
		$category_mapping['Hidden']['Example Workflow'] = 1;
		$category_mapping['Hidden']['MDCADD'] = 1;
		
		// if the workflow doesn't map to a category, put it in Hidden
		foreach ($workflows as $wf) {
			$found = false;
			foreach (array_keys($category_mapping) as $cat) {
				if (isset($category_mapping[$cat][$wf->name])) {
					$found = true;
				}	
			}
			if (!($found))
				$category_mapping['Hidden'][$wf->name] = 1;
		}			
		
		$this->view->mapped_categories = $category_mapping;

		// allow viewing of hidden category for some users 
		if (in_array($juser->username, array('admin', 'churas', 'dlee', 'yoyoman', 'wawong', 'aperez')))
			$this->view->show_hidden_categories = true;
		else
			$this->view->show_hidden_categories = false;

		// Output HTML
		if ($this->getError()) {
			foreach ($this->getErrors() as $error) {
				$this->view->setError($error);
			}
		}
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

	
	public function launchTask() {
		// get URI data
		//  Array ( ) [baseurl] => [option] => com_cws [task] => launch [controller] => results )
		//  Array ( [option] => com_cws [Itemid] => [task] => launch [period] => 4-wf4 )
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
		$seg = explode('-', $var['period']);

    
		$resultOfQuery = file_get_contents(API_DEFAULT . "/rest/workflows/" . $seg[0] . "?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
    $workflow = json_decode($resultOfQuery);
//		$wf2 = file_get_contents("nofilefield.json");
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
	/*
			class o_schema {};
			class o_options {};
			class o_data {};
	*/
	
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
		print_r($results);
		exit;

	}			
		
	public function processTask() {
		// If user tries to load "process" page with accessing it thru a form submission, send them to workflow listing
		if (!(isset($_POST['submit_job']))) {
			echo "<script type='text/javascript'>alert('Invalid form submission. Redirecting to Workflow list ...');";
			$redirectUrl = "/workflowservice";
			echo "window.location = '" . $redirectUrl . "'";
			echo "</script>\n";
			exit;
		}
		
		$this->view->json->id = null;
		$this->view->json->name = $_POST['_formCWS_jobname'];
		
		$user = JFactory::getUser();
		$this->view->json->owner = $user->username;
		
//		$this->view->json->status = "Running";
//		$this->view->json->createDate = n;
//		$this->view->json->submitDate = time();
//		$this->view->json->startDate = time();
//		$this->view->json->finishDate = time();
		
//		$this->view->json->downloadURL = "";
//		$this->view->json->estimatedCpuInSeconds = 0;
//		$this->view->json->estimatedDiskInBytes = 0;
//		$this->view->json->estimatedRunTime = 0;
//		$this->view->json->hasJobBeenSubmittedToScheduler = false;
		$form_array = array();

		// For willy test job, I removed the following file block. If the form has a file, is this needed?
//		array_push($form_array, array('name' => 'examplefile', 'value'=> 'jasdfasd'));

		foreach (array_keys($_POST) as $posted) {
			$fieldname = str_replace('_form', '', $posted);
			if (substr($posted, 0, 5) == '_form') {
				if (isset($_POST['isFile_' . str_replace('_form', '', $posted)])) {
					array_push($form_array, array('name' => $fieldname, 'value' => $_POST[$posted], 'isWorkspaceId' => 1));
				} else {
					array_push($form_array, array('name' => $fieldname, 'value' => $_POST[$posted]));
				}	
			}
		}

//		$this->view->json->parameters = array(array('name' => 'param1', 'value'=> 'jasdfasd'), array('name'=>'asdfasd', 'value'=> 'asdfasd'));
		$this->view->json->parameters = $form_array;
		$this->view->json->workflow->id = $_POST['workflowID'];
		$this->view->json->workflow->parentWorkflow = null;

		$newjson = json_encode($this->view->json);

	 	$url = API_DEFAULT . '/rest/jobs?runasuser=' . $user->username . '&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a';

		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $newjson);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
				   array('Content-Type:application/json')
				   );

		$results = curl_exec($ch);
		curl_close($ch);
		
		$results_json = json_decode($results);

		if ($results_json->parametersWithErrors) {

		        // Output the HTML
			$task = json_decode($results);
                        $this->view->task = $task;
                	$this->view->display();

	                /* Debug Information */
                        echo "<h2>Debug Information</h2>\n";
                        echo "<p><strong>POSTed form fields</strong></p>";
                        echo "<pre>\n";
                        print_r($_POST);
                        echo "</pre>\n";

                        echo "<p><strong>CURL data</strong></p>\n";
                        echo "<pre>\n";
                        print_r($this->view->json);
                        echo "</pre>\n";
                        /* End Debug */

		} elseif($results_json->id) {
			// If a successful job submission, redirect to the Jobs listing	
			if ($results) {

				$redirectUrl = urlencode(base64_encode("jobs"));
				$redirectUrl = "/workflowservice/jobs";
				echo "<script type='text/javascript'>";
				echo "window.location = '" . $redirectUrl . "'";
				echo "</script>\n";
				exit;
			}
		} else {

			$task = json_decode($results);
			$this->view->task = $task;

        // Output the HTML  
	        $this->view->display();	
	        /* Debug Information */
			echo "<h2>Debug Information</h2>\n";
			echo "<p><strong>POSTed form fields</strong></p>";
			echo "<pre>\n";
			print_r($_POST);
			echo "</pre>\n";
		
			echo "<p><strong>CURL data</strong></p>\n";
			echo "<pre>\n";
			print_r($this->view->json);
			echo "</pre>\n";
			/* End Debug */
	
		}		

		




	}
	

	public function processJSONTask() {
//JLog::add('** Start processJSON **');

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

	 	$url = API_DEFAULT . '/rest/jobs?runasuser=' . $user->username . '&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a';

		$ch=curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $newjson);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,
				   array('Content-Type:application/json')
				   );

		$results = curl_exec($ch);
		curl_close($ch);

		$results_json = json_decode($results);
		
//        JLog::add('Before returning status of the job submission');
       
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

		$juser = JFactory::getUser();
	
		$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?owner=" . $juser->username . "&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
		$files = json_decode($files_json);
		$this->view->files = $files;

        // Output the HTML  
        $this->view->display();		

	}

	/*** Generates JSON list of workspace files using pagination instructions from DataTables JS library ***/
	public function WorkspaceFilesJSONTask() {
		$document = JFactory::getDocument();
		$document->addStyleSheet( DATA_TABLES_CSS );
		$document->addScript( DATA_TABLES_JS );

		$juser = JFactory::getUser();

	/* these work:
	https://crbsworkflow.appspot.com:443//rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a
	https://crbsworkflow.appspot.com:443//rest/workspacefiles?userlogin=chris&usertoken=dc5902078cfa40b980229662c2e0c226
	*/



	$test = 1;

	if ($test) {
		$params = "rest/workspacefiles?owner=" . $juser->username . "&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a";
		if (JRequest::getVar('type')) {
			$params .= "&type=" . urlencode(JRequest::getVar('type'));
		}
		$params .= "&isfailed=" . JRequest::getVar('isfailed');

		$files_json = file_get_contents(API_DEFAULT . $params);
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

	/*** Show list of workspace files using pagination and ajax ***/
	public function MyFilesTask() {
		$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
		$files = json_decode($files_json);

		$this->view->files = $files_json;
		
        // Output the HTML  
        $this->view->display();		

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
	
		$juser = JFactory::getUser();

		$tasks_json = file_get_contents(API_DEFAULT . "/rest/jobs?owner=" . $juser->username . "&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a&noparams=true&noworkflowparams=true");
		$tasks = json_decode($tasks_json);
		$this->view->tasks = $tasks;

        // Output the HTML  
        $this->view->display();		

	}

	public function JobsJSONTask() {
	/*
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');
		$wf2 = file_get_contents("joblist.json");
		
		echo $wf2;
		exit;
	*/	
		
		$juser = JFactory::getUser();

		$tasks_json = file_get_contents(API_DEFAULT . "/rest/jobs?owner=" . $juser->username . "&userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a&noparams=true&noworkflowparams=true");
// Output the HTML  
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
		
		echo $tasks_json;
		exit;
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

		$task_json = file_get_contents(API_DEFAULT . "/rest/jobs/{$var['period']}?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
//		$task_json = file_get_contents("jobdetail.json");

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
