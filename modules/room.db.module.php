<?php
 // $Id$
 // note: room database functions
 // lic : GPL, v2

if (!defined("__ROOM_MODULE_PHP__")) {

define (__ROOM_MODULE_PHP__, true);

class roomMaintenance extends freemedMaintenanceModule {

	var $MODULE_NAME 	= "Room Maintenance";
	var $MODULE_VERSION = "0.1";

	var $record_name	= "Room";
	var $table_name 	= "room";

	var $variables  = array (
		"roomname",
		"roompos",
		"roomdescrip",
		"roomdefphy",
		"roomsurgery",
		"roombooking",
		"roomipaddr"
	);

	function roomMaintenance () {
		$this->freemedMaintenanceModule();
	} // end constructor roomMaintenance

	function form () {
		global $display_buffer;
    		global $roomdefphy, $roompos;
		foreach ($GLOBALS as $k => $v) global $$k; 

  switch ($action) { // inner switch
    case "addform":
      // do nothing
     break; // end of addform

    case "modform":
     $r = freemed_get_link_rec ($id, $this->table_name);
     extract ($r);
     break; // end of modform 
  } // end inner switch

  $display_buffer .= "
    <P>
    <FORM ACTION=\"$this->page_name\" METHOD=POST>
    <INPUT TYPE=HIDDEN NAME=\"id\"     VALUE=\"".prepare($id)."\">
    <INPUT TYPE=HIDDEN NAME=\"module\" VALUE=\"".prepare($module)."\">
    <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"".
     ( ($action=="addform") ? "add" : "mod" )."\">
  
  ".html_form::form_table ( array (
    _("Room Name") =>
    "<INPUT TYPE=TEXT NAME=\"roomname\" SIZE=20 MAXLENGTH=20
     VALUE=\"".prepare($roomname)."\">",

    _("Location") =>
    "<SELECT NAME=\"roompos\">
    ".freemed_display_facilities ("roompos", true)."
    </SELECT>",

    _("Description") =>
    "<INPUT TYPE=TEXT NAME=\"roomdescrip\" SIZE=20 MAXLENGTH=40
     VALUE=\"".prepare($roomdescrip)."\">",

    _("Default Provider") =>
    freemed_display_selectbox (
    $sql->query ("SELECT * FROM physician"),
    "#phylname#, #phyfname#",
    "roomdefphy"),

    _("Surgery Equipped") =>
    "<INPUT TYPE=CHECKBOX NAME=\"roomsurgery\" VALUE=\"y\"
     ".( ($roomsurgery=="y") ? "CHECKED" : "" ).">",

    _("Booking Enabled") =>
    "<INPUT TYPE=CHECKBOX NAME=\"roombooking\" VALUE=\"y\" 
     ".( ($roombooking=="y") ? "CHECKED" : "" ).">",

    _("IP Address") =>
    "<INPUT TYPE=TEXT NAME=\"roomipaddr\" SIZE=16 MAXLENGTH=15
     VALUE=\"".prepare($roomipaddr)."\">"

    )
   ); 

  $display_buffer .= "
    <CENTER>
    <INPUT TYPE=SUBMIT VALUE=\" ".
      ( ($action=="addform") ? _("Add") : _("Modify") )." \">
    <INPUT TYPE=RESET  VALUE=\""._("Clear")."\">
    </CENTER></FORM>
  ";
	} // end function roomMaintenance->form

	function view () {
		global $display_buffer;
		global $sql;
		$display_buffer .= freemed_display_itemlist (
			$sql->query ("SELECT roomname,roomdescrip,id ".
				"FROM $this->table_name ORDER BY roomname"),
			$this->page_name,
			array (
				_("Name")		=>	"roomname",
				_("Description")	=>	"roomdescrip"
			),
			array (
				"",
				_("NO DESCRIPTION")
			)
		);
	} // end function roomMaintenance->view

} // end class roomMaintenance

register_module ("roomMaintenance");

} // end if not defined

?>
