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

define("API_DEFAULT", "https://crbsworkflow.appspot.com/");
		
ximport('Hubzero_Controller');


/**
 * Usage controller class for results
 */
class CwsControllerResults extends Hubzero_Controller
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
//		$workflows = json_decode($build_url));
		$workflows = json_decode(file_get_contents(API_DEFAULT . "/rest/workflows?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a"));
		$this->view->workflows = $workflows;
		
		foreach($workflows as $wf) {
//			echo $wf->name . "<br />\n";
		}	
		
		// Output HTML
		if ($this->getError()) 
		{
			foreach ($this->getErrors() as $error)
			{
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
	$uploads_dir = '/Users/mchiu/Sites/hubzero_fresh/uploads';
	$new_file = time() . "_" .	$_FILES["_formexamplefile"]["name"];
	if (move_uploaded_file($_FILES["_formexamplefile"]["tmp_name"], $uploads_dir . "/" . $new_file)) {
		echo "http://hubzero_fresh/cws/preview/" . str_replace(".json", "", $new_file);
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
	//Array ( ) [baseurl] => [option] => com_cws [task] => launch [controller] => results )
/// Array ( [option] => com_cws [Itemid] => [task] => launch [period] => 4-wf4 )

		$router =& JSite::getRouter();
		$var = $router->getVars();

		$seg = explode('-', $var['period']);
//		$workflow = json_decode(file_get_contents(API_DEFAULT . "/rest/workflows/" . $seg[0] . "?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a"));
		$wf2 = file_get_contents("wf2.json");
		$workflow = json_decode($wf2);

// Instantiate a new view  
    //    $view = new JView( array('name'=>'launch') );  
          
        // Pass the view any data it may need  
      //  $view->greeting = 'Hello, World!';  
          
       $this->view->workflow = $workflow;

        // Output the HTML  
        $this->view->display();  	
	}
	
	
	

	public function crazyTask() {
	//Array ( ) [baseurl] => [option] => com_cws [task] => launch [controller] => results )
/// Array ( [option] => com_cws [Itemid] => [task] => launch [period] => 4-wf4 )

		$router =& JSite::getRouter();
		$var = $router->getVars();

		$seg = explode('-', $var['period']);
		$workflow = json_decode(file_get_contents(API_DEFAULT . "/rest/workflows/" . $seg[0] . "?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a"));
//		$wf2 = file_get_contents("wf2.json");
//		$workflow = json_decode($wf2);

// Instantiate a new view  
    //    $view = new JView( array('name'=>'launch') );  
          
        // Pass the view any data it may need  
      //  $view->greeting = 'Hello, World!';  
          
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
		$uploads_dir = '/Users/mchiu/Sites/hubzero_fresh/uploads';
		$new_file = "$uploads_dir/" . $_FILES["examplefile"]["name"];
		if (move_uploaded_file($_FILES["examplefile"]["tmp_name"], $new_file)) {


			if (isset($_FILES['examplefile'])) {
				$md5 = md5_file($new_file);
				$info = new SplFileInfo($new_file);

				$json = '{"createDate" : null,"description" : "text about file","blobKey" : null,"md5" : "' . $md5 . '","deleted" : false, "dir" : false, "sourceTaskId" : null, 
				"uploadURL" : null, "owner" : "bob", "id" : null, "type" : "' . $info->getExtension() . '", "path" : null, "name" : "' . $_FILES['examplefile']['name'] . '", "size" : 123123}';

				$results = registerWorkspaceFile($json);
				$res_obj = json_decode($results);

				// Create a cURL handle
				$ch = curl_init($res_obj->uploadURL);

				// Assign POST data
				$post = array($res_obj->id => '@'. $new_file);
				curl_setopt($ch, CURLOPT_POST,1);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
				curl_setopt($ch, CURLOPT_SAFE_UPLOAD, 1);
				// Execute the handle
				curl_exec($ch);


			}	
		}
		
		$this->view->json->id = null;
		$this->view->json->name = $_POST['_formCWS_taskname'];
		$this->view->json->owner = $_POST['_formCWS_user'];

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
		array_push($form_array, array('name' => 'examplefile', 'value'=> 'jasdfasd'));
		foreach (array_keys($_POST) as $posted) {
			if (($posted !== '_formCWS_taskname') && ($posted !== '_formCWS_user') && ($posted !== '_formCWS_outputdir')) {
				if (substr($posted, 0, 5) == '_form') {
					array_push($form_array, array('name' => str_replace('_form', '', $posted), 'value' => $_POST[$posted]));
				}
			}
		}

//		$this->view->json->parameters = array(array('name' => 'param1', 'value'=> 'jasdfasd'), array('name'=>'asdfasd', 'value'=> 'asdfasd'));
		$this->view->json->parameters = $form_array;
		$this->view->json->workflow->id = $_POST['workflowID'];
		$this->view->json->workflow->parentWorkflow = null;

		$newjson = json_encode($this->view->json);

	 	$url = API_DEFAULT . '/rest/jobs?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a';

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

		if ($results->parametersWithErrors) {
			var_dump($results->parametersWithErrors);
		} else {
			$task = json_decode($results);
			$this->view->task = $task;

        // Output the HTML  
	        $this->view->display();		
		}		
	}
	
	/*** Show list of workspace files ***/
	public function WorkspaceFilesTask() {
		$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
		$files = json_decode($files_json);

		$this->view->files = $files;

        // Output the HTML  
        $this->view->display();		

	}

	/*** Generates JSON list of workspace files using pagination instructions from DataTables JS library ***/
	public function WorkspaceFilesJSONTask() {
		$files_json = file_get_contents(API_DEFAULT . "/rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");

	   // Output the HTML  
		JFactory::getDocument()->setMimeEncoding( 'application/json' );
		JResponse::setHeader('Content-Disposition','attachment;filename="progress-report-results.json"');

		echo '{
	  "sEcho": 0,
	  "recordsTotal": 45,
	  "recordsFiltered": 45,
	  "data":  ';
		if (isset($_GET['start'])) {
			$decoded = json_decode($files_json);
			$json = array();
			for ($i=$_GET['start']; $i<($_GET['start'] + $_GET['length']); $i++) {
				$decoded[$i]->DT_RowId = $decoded[$i]->id . "_" . $decoded[$i]->name;
				array_push($json, $decoded[$i]);
			}
		  echo json_encode($json);
		} else {
			echo $files_json;
		}	
		echo "}";

		JFactory::getApplication()->close(); // or jexit();	
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
		$tasks_json = file_get_contents(API_DEFAULT . "/rest/jobs?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a&noparams=true&noworkflowparams=true");
		$tasks = json_decode($tasks_json);
		$this->view->tasks = $tasks;

//		$tasks = file_get_contents("http://hubzero_fresh/biglist.json");
//		$this->view->tasks = json_decode($tasks);
		

        // Output the HTML  
        $this->view->display();		

	}

	public function JobDetailsTask() {
		$router =& JSite::getRouter();
		$var = $router->getVars();
		$task_json = file_get_contents(API_DEFAULT . "/rest/jobs/{$var['period']}?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a");
		$task = json_decode($task_json);
		$this->view->task = $task;

        // Output the HTML  
        $this->view->display();		

	}
}

	function registerWorkspaceFile($json) {
	
	 	$url = API_DEFAULT . '/rest/workspacefiles?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a';
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
		
		return $results;	
		
		/*
		HTTP/1.1 200 OK 
		Content-Type: application/json 
		Vary: Accept-Encoding 
		Date: Wed, 25 Jun 2014 23:33:52 GMT 
		Server: Google Frontend 
		Cache-Control: private 
		Alternate-Protocol: 443:quic 
		Transfer-Encoding: chunked 
		
		{"name":"foo.png","id":5801718327541760,"type":"png","size":123123,"path":null,"owner":"bob","description":"text about file","uploadURL":"https://crbsworkflow.appspot.com/_ah/upload/?userlogin=mikechiu&usertoken=67cecab615914b2494830ef116a4580a/AMmfu6bNE2XPu_USIPVlwndA1pDwqJKzqBzCoEH67QUc7hBugkCYTpFV5ly5g1pNugBY1kyeWhJMytFEcoplkXMCaM-pSTHHAdgnfiX9_hBdBdx2HVyxd1LaVhX78nlysS2DctUM8Z3t-8AwdyCQpBeuWgpRte-Hdt68a4JAkXMp1-8q_drWzNpAxDPvfhyws5uFVAnqsMt_mM-XeBuqeREAoexOvW6ouA/ALBNUaYAAAAAU6tetwVFYBm1FNr8zA0Fvylx30zXDxjF/","createDate":1403739231841,"md5":null,"deleted":false,"dir":false,"blobKey":null,"sourceTaskId":null}1
		*/

	}



	