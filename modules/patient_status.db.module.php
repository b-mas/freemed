<?php
 // $Id$
 // note: patient status functions
 // lic : GPL, v2

if (!defined("__PATIENT_STATUS_MODULE_PHP__")) {

define (__PATIENT_STATUS_MODULE_PHP__, true);

class patientStatusMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME	= "Patient Status Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name 	= "Patient Status";
	var $table_name		= "ptstatus";

	var $variables 		= array (
		"ptstatus",
		"ptstatusdescrip"
	);

	function patientStatusMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor patientStatusMaintenance

	function addform () { $this->view(); }

	function modform () {
		global $display_buffer;
		reset($GLOBALS);
		while(list($k,$v)=each($GLOBALS)) global $$k;
		 // grab record number "id"
  		$result = $sql->query("SELECT * FROM $this->table_name WHERE
    		(id='".addslashes($id)."')");

  $r = $sql->fetch_array($result);
  extract ($r);
		$display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"mod\"> 
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\"> 
    <INPUT TYPE=HIDDEN NAME=\"id\"   VALUE=\"".prepare($id)."\"  >

    ".html_form::form_table ( array (

    _("Status") =>
    "<INPUT TYPE=TEXT NAME=\"ptstatus\" SIZE=3 MAXLENGTH=2
     VALUE=\"".prepare($ptstatus)."\">",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"ptstatusdescrip\" SIZE=20 MAXLENGTH=30
     VALUE=\"".prepare($ptstatusdescrip)."\">"

    ) )."

    <P>
    <CENTER>
     <INPUT TYPE=SUBMIT VALUE=\" "._("Modify")." \">
     <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER>

    </FORM>
  ";

  $display_buffer .= "
    <P>
    <CENTER>
    <A HREF=\"$this->page_name\"
     >"._("Abandon Modification")."</A>
    </CENTER>
  ";
	} // end function patientStatusMaintenance->modform()

	function view () {
		global $display_buffer;
		global $sql;
 		$display_buffer .= freemed_display_itemlist (
 			$sql->query ("SELECT ptstatusdescrip,ptstatus,id ".
				"FROM $this->table_name ORDER BY ptstatusdescrip,ptstatus"),
			$this->page_name,
			array (
				_("Status")			=>	"ptstatus",
				_("Description")	=>	"ptstatusdescrip"
			),
			array (
				"", _("NO DESCRIPTION")
			)
		);  
		$this->_addform();
	} // end function patientStatusMaintenance->view()

	function _addform () {
		global $display_buffer;
		global $module;
		$display_buffer .= "
		<CENTER>
    	<FORM ACTION=\"$this->page_name\">
			<INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"add\">
			<INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
		".html_form::form_table ( array (
			_("Status") =>
				html_form::text_widget ("ptstatus", 2),
			_("Description") =>
				html_form::text_widget ("ptstatusdescrip", 20)
		) )."
		<BR>	
			<INPUT TYPE=SUBMIT VALUE=\""._("Add")."\">
		</FORM>
		</CENTER>
		";
	} // end function patientStatusMaintenance->addform()

} // end class patientStatusMaintenance

register_module ("patientStatusMaintenance");

} // end if not defined

?>
