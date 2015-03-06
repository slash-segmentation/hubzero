hubzero
=======

Assumes that the Hubzero CMS (Joomla) has already been installed. If not, please download the file (http://packages.hubzero.org/deb/pool/main/h/hubzero-cms/hubzero-cms_20130821.1.orig.tar.gz - 15M) and install it.

The main work is the CRBS Workflow Service, which has been written as a Joomla component. The Com_workflowservice folder should be installed in the hubzero "components" directory. You will need to use the Extension Manager to "discover" the new component. It helps to first "purge cache" before trying the "discover". There isn't an "admin" section to the component, so it will give an error, but it doesn't affect things ...

