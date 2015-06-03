hubzero
=======

Contains CRBS Workflow Service HubZero component.

Requirements

* Hubzero 1.3 or at a minimum Hubzero CMS (Joomla) installed.  If not, please download the file (http://packages.hubzero.org/deb/pool/main/h/hubzero-cms/hubzero-cms_20130821.1.orig.tar.gz - 15M) and install it.

* Alpaca (http://www.alpacajs.org) version 1.1.3 (download alpaca-distribution.zip) and unzip into alpaca folder under (MAIN_HUB_ZERO_DIR/media)

* DataTables (http://www.datatables.net) version 1.10.1 (http://datatables.net/releases/DataTables-1.10.1.zip)

The main work is the CRBS Workflow Service, which has been written as a Joomla component. The original component did not have an administrative component. It's best to use the Extension Manager to uninstall the component before proceeding with installating the latest version.

To install the current version, first download the "com_workflowservice_installer.zip" file. Then, use the Extension Manager :: Install to upload the package file, which is the zip you just downloaded. Click "Upload & Install", and it should take care of the rest. Once installed, you should go to the new Components::CRBS Workflow Service menu and configure the component. The URL might work as is, but you will for sure need to change the username:password. Once it's configured, you will need to add workflow categories and use the admin tool to select what workflows belong in each category.
