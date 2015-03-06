<?php /**  * @copyright	Copyright (C) 2013 JoomlaTemplates.me - All Rights Reserved. **/ defined( '_JEXEC' ) or die( 'Restricted access' );
$scrolltop		= $this->params->get('scrolltop');
$logo			= $this->params->get('logo');
$logotype		= $this->params->get('logotype');
$sitetitle		= $this->params->get('sitetitle');
$sitedesc		= $this->params->get('sitedesc');
$menuid			= $this->params->get('menuid');
$animation		= $this->params->get('animation');
$app			= JFactory::getApplication();
$doc			= JFactory::getDocument();
$templateparams	= $app->getTemplate(true)->params;
$menu = $app->getMenu();
$config = JFactory::getConfig();
$juser  = JFactory::getUser();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>">
<head>
<jdoc:include type="head" />
<?php if ( version_compare( JVERSION, '3.0.0', '<' ) == 1) { ?>
<!--<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>-->
<?php } else { JHtml::_('bootstrap.framework');JHtml::_('bootstrap.loadCss', false, $this->direction);}?>
<?php include "functions.php"; ?>
<meta name="viewport" content="width=device-width" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/styles.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/bootstrap.min.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/font-awesome.min.css" type="text/css" />
<!--[if IE 7]><link rel="stylesheet" href="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/css/font-awesome-ie7.min.css" type="text/css" /><![endif]-->
<!--[if lt IE 9]><script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script><script src="http://css3-mediaqueries-js.googlecode.com/svn/trunk/css3-mediaqueries.js"></script><![endif]-->
<link href='https://fonts.googleapis.com/css?family=Oswald' rel='stylesheet' type='text/css'>
<link href='https://fonts.googleapis.com/css?family=Ubuntu' rel='stylesheet' type='text/css'>
<?php if ($scrolltop == 'yes' ) : ?>
	<script type="text/javascript" src="<?php echo $this->baseurl ?>/templates/<?php echo $this->template ?>/js/scroll.js"></script>
<?php endif; ?>
</head>
<body class="background">

<div id="header-wrap" class="clr">
    	<div id="header" class="container row clr">   
            <div id="logo" class="col span_5">
				<?php if ($logotype == 'image' ) : ?>
                <?php if ($logo != null ) : ?>
            <a href="<?php echo $this->baseurl ?>"><img src="<?php echo $this->baseurl ?>/<?php echo htmlspecialchars($logo); ?>" alt="<?php echo htmlspecialchars($templateparams->get('sitetitle'));?>" /></a>
                <?php else : ?>
            <a href="<?php echo $this->baseurl ?>/"><img src="<?php echo $this->baseurl; ?>/templates/<?php echo $this->template; ?>/images/logo.png" border="0"></a>
                <?php endif; ?><?php endif; ?> 
                <?php if ($logotype == 'text' ) : ?>
            <a href="<?php echo $this->baseurl ?>"><?php echo htmlspecialchars($sitetitle);?></a>
                <?php endif; ?>
                <?php if ($sitedesc !== '' ) : ?>
                <div id="site-description"><?php echo htmlspecialchars($sitedesc);?></div>
                <?php endif; ?>  
            </div><!-- /logo -->
			<?php if ($this->countModules('menu')) : ?>
            <div id="navbar-wrap" class="col span_7">
                <nav id="navbar">

                    <div id="navigation"> 
                    	<?php // following block came from hubbasic2013 templates ?>
						<div id="account" role="navigation" class="cf">
							<?php if (!$juser->get('guest')) { ?>
								<ul class="menu <?php echo (!$juser->get('guest')) ? 'loggedin' : 'loggedout'; ?>">
									<li>
										<div id="account-info">
											<?php
											$profile = \Hubzero\User\Profile::getInstance($juser->get('id'));
											?>
											<img src="<?php echo $profile->getPicture(); ?>" alt="<?php echo $juser->get('name'); ?>" width="30" height="30" />
											<a class="account-details" href="<?php echo JRoute::_('index.php?option=com_members&id=' . $juser->get('id')); ?>">
												<?php echo stripslashes($juser->get('name')); ?> 
												<span class="account-email"><?php echo $juser->get('email'); ?></span>
											</a>
										</div>
										<ul>
											<li id="account-dashboard">
												<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $juser->get('id') . '&active=dashboard'); ?>"><span><?php echo "Dashboard"; ?></span></a>
											</li>
											<li id="account-profile">
												<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $juser->get('id') . '&active=profile'); ?>"><span><?php echo "Profile"; ?></span></a>
											</li>
											<li id="account-messages">
												<a href="<?php echo JRoute::_('index.php?option=com_members&id=' . $juser->get('id') . '&active=messages'); ?>"><span><?php echo "Messages"; ?></span></a>
											</li>
											<li id="account-logout">
												<a href="<?php echo JRoute::_('index.php?option=com_users&view=logout'); ?>"><span><?php echo "Logout"; ?></span></a>
											</li>
										</ul>
									</li>
								</ul>
							<?php } else { ?>
								<ul class="menu <?php echo (!$juser->get('guest')) ? 'loggedin' : 'loggedout'; ?>">
									<li id="account-login">
										<a href="<?php echo JRoute::_('index.php?option=com_users&view=login'); ?>" title="<?php echo JText::_('TPL_HUBBASIC_LOGIN'); ?>"><?php echo "Login"; ?></a>
									</li>
									<?php
									$usersConfig = JComponentHelper::getParams('com_users');
									if ($usersConfig->get('allowUserRegistration') != '0') : ?>
										<li id="account-register">
											<a href="<?php echo JRoute::_('http://ncmir.ucsd.edu/collaborator/application-UserInfo.php'); ?>" target="_blank" title="<?php echo JText::_('TPL_HUBBASIC_SIGN_UP'); ?>"><?php echo "Register"; ?></a>
										</li>
									<?php endif; ?>
								</ul>
							<?php } ?>
							</div><!-- / #account -->
                        <jdoc:include type="modules" name="menu" style="menu" />
                     </div>            
                </nav>
            </div>
            <?php endif; ?>             
    	</div>
<?php if (is_array($menuid) && !is_null($menu->getActive()) && in_array($menu->getActive()->id, $menuid, false)) { ?>
            <div id="slide-wrap" class="container row clr">
                    <?php include "slideshow.php"; ?>
            </div>
<?php } ?>
</div>
<?php $menu = $app->getMenu(); if ($menu->getActive() == $menu->getDefault()) { ?>
<div class="company"><?php jlink(); ?></div>
<?php } ?>
<div id="wrapper"> 
        <?php if ($this->countModules('breadcrumbs')) : ?>
        <div class="container row clr">
        	<jdoc:include type="modules" name="breadcrumbs"  style="none"/>
        </div>
        <?php endif; ?>
        
		<?php if ($this->countModules('user1')) : ?>
            <div id="user1-wrap"><div id="user1" class="container row clr">
            	<jdoc:include type="modules" name="user1" style="usergrid" grid="<?php echo $user1_width; ?>" />
            </div></div>
        <?php endif; ?>
                    
<div id="box-wrap" class="container row clr">
	<div id="main-content" class="row span_12">
							<?php if ($this->countModules('left')) : ?>
                            <div id="leftbar-w" class="col span_3 clr">
                            	<div id="sidebar">
                                	<jdoc:include type="modules" name="left" style="grid" />
                            	</div>
                            </div>
                            <?php endif; ?>
                                <div id="post" class="col span_<?php echo $compwidth ?> clr">
                                    <div id="comp-wrap">
                                        <jdoc:include type="message" />
                                        <jdoc:include type="component" />
                                    </div>
                                </div>
							<?php if ($this->countModules('right')) : ?>
                            <div id="rightbar-w" class="col span_3 clr">
                            	<div id="sidebar">
                                	<jdoc:include type="modules" name="right" style="grid" />
                            	</div>
                            </div>
                            <?php endif; ?>
	</div>
</div>
</div>
		<?php if ($this->countModules('user2')) : ?>
            <div id="user2-wrap"><div id="user2" class="container row clr">
            	<jdoc:include type="modules" name="user2" style="usergrid" grid="<?php echo $user2_width; ?>" />
            </div></div>
        <?php endif; ?>
<?php include "social.php"; ?>        
<div id="footer-wrap"  class="container row clr" >
        <?php if ($this->countModules('copyright')) : ?>
            <div class="copyright">
                <jdoc:include type="modules" name="copyright"/>
            </div>
        <?php endif; ?>
        <?php if ($this->countModules('footer-menu')) : ?>
            <div id="footer-nav">           
				<jdoc:include type="modules" name="footer-menu" style="none" />
            </div>
        <?php endif; ?>                
</div>
</body>
</html>