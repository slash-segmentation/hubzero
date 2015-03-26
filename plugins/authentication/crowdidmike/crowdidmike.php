<?php
/**
 * @version    crowdidmike.php 1
 * @package    Crowdidmike
 * @subpackage Plugins
 * @license    GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.log.log');
jimport('joomla.plugin.plugin');
jimport('joomla.event.plugin');
jimport('joomla.error.log');

include_once "/Users/mchiu/sites/example130/libraries/httpful.phar";

class plgAuthenticationCrowdidmike extends JPlugin
{
    /**
     * Constructor
     *
     * For php4 compatability we must not use the __constructor as a constructor for plugins
     * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
     * This causes problems with cross-referencing necessary for the observer design pattern.
     *
     * @param object $subject The object to observe
     * @since 1.5
     */
    public function plgAuthenticationCrowdidmike(& $subject, $config) {
        parent::__construct($subject, $config);
        JLog::addLogger(array('text_file' => 'debug.crowdidmikemore.log'));
        JLog::add('crowdidmike 0.01 Start Crowdidmike logging');
        
    }


	/**
	 * Perform logout (not currently used)
	 *
	 * @access	public
	 * @return	void
	 */
	public function logout()
	{
		// This is handled by the JS API, and cannot be done server side
		// (at least, it cannot be done server side, given our authentication workflow
		// and the current limitations of the PHP SDK).
	      JLog::add('************** function LOGOUT - HERE I AM!!!!!!!!!!!!!!!!!!!!!!');
	}

	/**
	 * Check login status of current user with regards to facebook
	 *
	 * @access	public
	 * @return	Array $status
	 */
	public function status()
	{
	      JLog::add('mike status ...');

	}

    public function login(&$credentials, &$options) {
        JLog::add('AUTHTESTEcrowdidmike 0.01 Start Crowdidmike logging');

		if (isset($_POST['userid'])) {
			$juri    = JURI::getInstance();
			$service = trim($juri->base(), DS);

			$providerData = getProviderData($this->params->get('providerUrl'));
			$authUrl = $this->params->get('idUrl') . $_POST['userid'];
        JLog::add('authurl = ' . $authUrl);

			// Where the user should land after authentication 
			//(should be in the same domain as $domain)

			//
			//	$returnto = $service . '/index.php?option=com_users&task=' . $task . $view->return;
			$returnto = $service . '/index.php?option=com_users&task=user.login&return=L21lbWJlcnMvbXlhY2NvdW50&authenticator=crowdidmike';

			//$returnto = $service . '/index.php?option=com_users&authenticator=crowdidmike&task=' . $task . '&return=' . $view->return;

			//https://openid.crbs.ucsd.edu/openidserver/secure/interaction/allowauthentication!doAllow.action?profileID=98308&atl_token=3f2f32974ad82a26f9ef8c61aad9b9f7ba3db54f
			/* We want to make sure the user that tried to
			login is the same as the user that will go to the landing page.
			This is why we save some of the information in session variables
			to check later on.

			You are required to check yourself if cookies are supported on the client!
			*/

	  $_SESSION['openid.authUrl']  = $authUrl;
	  $_SESSION['openid.idUrl'] = $this->params->get('idUrl') . $_POST['userid'];
	  $_SESSION['openid.confirmed'] = false;


	$providerURL = $this->params->get('providerUrl');
	  $location = $providerURL . '?openid.mode=checkid_setup';
	  $location .= '&openid.assoc_handle=shared1';
	  $location .= '&openid.ns=http%3A%2F%2Fspecs.openid.net%2Fauth%2F2.0'; 
	  $location .= '&openid.ns.pape=http%3A%2F%2Fspecs.openid.net%2Fextensions%2Fpape%2F1.0'; 
	  $location .= '&openid.claimed_id=' . urlencode($authUrl); 
	  $location .= '&openid.ns.sreg=http%3A%2F%2Fopenid.net%2Fextensions%2Fsreg%2F1.1';
	  $location .= '&openid.identity=' . urlencode($authUrl); 
	  $location .= '&openid.pape.preferred_auth_policies=http%3A%2F%2Fschemas.openid.net%2Fpape%2Fpolicies%2F2007%2F06%2Fmulti-factor+http%3A%2F%2Fschemas.openid.net%2Fpape%2Fpolicies%2F2007%2F06%2Fmulti-factor-physical+http%3A%2F%2Fschemas.openid.net%2Fpape%2Fpolicies%2F2007%2F06%2Fphishing-resistant'; 
	  $location .= '&openid.return_to=' . urlencode($returnto);
	  $location .= '&openid.realm=' . $service;
	  $location .= '&openid.sreg.optional=email,nickname,fullname';

	  header('Location: ' . $location);
	} // end if isset

		$router =& JSite::getRouter();
		$var = $router->getVars();

		// first, check if the provider provided username exists in the database
		$db = &JFactory::getDBO();
		$query = "SELECT username FROM #__users WHERE email = '" . $var['openid_ext1_email'] . "'";
		$db->setQuery($query);
		$dbresult = $db->loadObject();
		if ($dbresult) {

			$login_succeeded = false;
  
			$juser = JUser::getInstance();
	//		if ($id = intval(JUserHelper::getUserId($dbresult->username))) {
		
			 // Add the access token to the session
			$jsession = JFactory::getSession();

			$response->status = JAuthentication::STATUS_SUCCESS;
			$response->email = $var['openid_ext1_email'];
			$response->fullname = $var['openid_ext1_fullname'];
			$response->username = $dbresult->username;
			$response->error_message = '';
			$response->id = $id;

			JLog::add('What is the response: ' . var_export($response, true));

			$_SESSION['openid.email']  = $var['openid_ext1_email'];
			$_SESSION['openid.fullname'] = $var['openid_ext1_fullname'];
			$_SESSION['openid.username'] = $dbresult->username;


/******** NEED TO CHANGE 'emailConfirmed' to 1 instead of the -randomvalue in jos_xprofiles table ***/
			return $response;
		} elseif (isset($var['openid_ext1_nickname'])) {
			JLog::add('creating new user ' . $var['openid_ext1_nickname']);
			$user = JUser::getInstance();
			$user->set('id', 0);
			$user->set('name', $var['openid_ext1_fullname']);
			$user->set('username', $var['openid_ext1_nickname']);
			$user->set('email', $var['openid_ext1_email']);
			$user->save();

			$id = intval(JUserHelper::getUserId($var['openid_ext1_nickname']));

			JLog::add('Should have created a new user ...' . $id);
			echo "newuser with $id <br />\n";
                        $response->status = JAuthentication::STATUS_SUCCESS;
                        $response->email = $var['openid_ext1_email'];
                        $response->fullname = $var['openid_ext1_fullname'];
                        $response->username = $var['openid_ext1_nickname'];
                        $response->error_message = '';
                        $response->id = $id;

			/* set the emailConfirmation flag */
			// Load the profile
			$profile = new \Hubzero\User\Profile();
			$profile->load($id);

			$profile->set('emailConfirmed', 1);

			// Save the changes
			if (!$profile->update())
			{
				JError::raiseWarning('', $profile->getError());
				return false;
			}
		
					

			JLog::add(' Before returning the fatal response');
JLog::add('Here is the data before fatal response' . var_export($response, true));

 $_SESSION['openid.email']  = $var['openid_ext1_email'];
                        $_SESSION['openid.fullname'] = $var['openid_ext1_fullname'];
                        $_SESSION['openid.username'] = $var['openid_ext1_nickname'];


			return $response;
		} else {
JLog::add('I hope this is skipping the block that creates the new user');
echo "I hope this skipped the new user block";
		}

//      $user->id = $id;
//      $response->id = $user->id;
	exit;
 }

    
    public function display($view, $tpl) {
            JLog::add('AUTHTESTEcrowdidmike 0.01 Start Crowdidmike logging');

		$juri = JURI::getInstance();
		$service = trim($juri->base(), DS);

echo '
	<header id="content-header">
		<h2>Login</h2>
	</header>
	<div class="hz_user">
	<div class="auth">
		<div class="person"></div>
		<div class="default"></div>
			<div class="hz" style="display: block">
				<div class="instructions">Enter your CRBS CROWD username:</div>

					<form method="post" action="' . $service . '/login">
					<input type="hidden" name="option" value="com_users" />
					<input type="hidden" name="authenticator" value="crowdidmike" />
					<input type="hidden" name="task" value="user.login" />
					<div class="input-wrap">
						<input type="text" name="userid" value="" size="80" />
					</div>
					<div class="submission">
						<input type="hidden" />
						<input type="submit" value="Log in" class="btn btn-primary" />
					</div>

			</div>
		</div>
	</div>	
</div>		
';
	}

    
    /**
    * This method should handle any authentication and report back to the subject
    *
    * @access      public
    * @param   array       $credentials Array holding the user credentials
    * @param       array   $options     Array of extra options
    * @param       object  $response        Authentication response object
    * @return      boolean
    */
    public function onAuthenticate( $credentials, $options, &$response )
    {
        JLog::add('AUTHTESTEcrowdidmike 0.01 Start Crowdidmike logging');
      JLog::add('************** function onAuthenticate - HERE I AM!!!!!!!!!!!!!!!!!!!!!!');
      JLog::add('login successfull - onAuthenticate, returning: ' . var_export($response, true));
print_r($response);

echo "about to exit from onAuthenticate";

      return $this->onUserAuthenticate($credentials, $options, $response);
    }

    /**
     * This method should handle any authentication and report back to the subject
     *
     * @access    public
     * @param     array     $credentials    Array holding the user credentials ('username' and 'password')
     * @param     array     $options        Array of extra options
     * @param     object    $response       Authentication response object
     * @return    boolean
     * @since 1.5
     */
    public function onUserAuthenticate( $credentials, $options, &$response )
    {
        JLog::add('AUTHTESTEcrowdidmike 0.01 Start Crowdidmike logging');
      JLog::add('************** function onUserAuthenticate - HERE I AM!!!!!!!!!!!!!!!!!!!!!!');

      $response->type = 'crowdidmike';
      $response->password_clear = "";
      JLog::add('login successfull, returning: ' . var_export($response, true));

// hardcode ...



echo "about to exit from onUserAuthenticate";



      // set response values for joomla auth
      $response->email = (string) $_SESSION['openid.email'];
      $response->fullname = (string) $_SESSION['openid.fullname'];
      $response->username = (string) $_SESSION['openid.username'];
      $response->status = JAUTHENTICATE_STATUS_SUCCESS;
      $response->error_message = '';

      // finally export our token as cookie
//      JLog::add('set cookie ' . $cookieName . ' = ' . $token);
//      setcookie($cookieName,$token, 0, "/", $cookieDomain,false,true);


        return true; // do not more for admin user


 JLog::add('response: ' . var_export($response, true));




      $login_succeeded = false;
      if (array_key_exists('immediate', $options) and $options['immediate']) {
        $login_succeeded = $this->doSSOLogin($credentials, $options, $response);
      }
      else {
        $login_succeeded = $this->doCrowdLogin($credentials, $options, $response);
      }
            if ($credentials['username'] == "admin") {
        JLog::add('admin login, neither check user nor groups');
        return login_succeeded; // do not more for admin user
      }
      if (! $login_succeeded) {
        $this->checkDeleteUser($credentials);
        return false;
      }
      $juser = JUser::getInstance();
      if ($id = intval(JUserHelper::getUserId($response->username))) {
      }
      else {
        JLog::add('creating new user ' . $response->username);
        $user->set('id', 0);
                                $user->set('name', $response->fullname);
                                $user->set('username', $response->username);
                                $user->set('email', $response->email);
              $user->save();
              $id = intval(JUserHelper::getUserId($response->username));
      }
      $user->id = $id;
      $response->id = $user->id;

      $this->handleGroups($user, $credentials, $options, $response);
      return true;
    }
}


function getProviderData($providerUrl) {

    // This sends an associate requests, which should return the provider secret
    return doPostRequest(array(
        'openid.mode' => 'associate'
    ), $providerUrl);

}

function doPostRequest($vars, $providerUrl) {
    $r = \Httpful\Request::post ($providerURL);
    $vars2 = array();
    foreach($vars as $k=>$v) $vars2[] = $k.'=' . urlencode($v);

    $r->Request(implode('&',$vars2));
    $data = $r->exec();
    $data = explode("\n",$data);
    $rvars = array();
    foreach($data as $l) {

        if (!$l) break;
        $yo = explode(':',$l,2);
        $rvars[$yo[0]] = $yo[1];

    }

    return $rvars;

}


?>

