<?php
 // note: patient database functions
 // code: jeff b (jeff@univrel.pr.uconn.edu)
 //       adam b (gdrago23@yahoo.com)
 //       some small stuff by: max k <amk@span.ch>
 // lic : GPL, v2
 
$page_name="patient.php"; // for help info, later
$record_name="Patient";    // compatibility with API functions
include ("lib/freemed.php");
include ("lib/API.php");
include ("lib/calendar-functions.php");

// Create user object
if (!is_object($this_user)) $this_user = new User;

  if ( ($id>0) AND 
       ($action != "addform") AND ($action != "add") AND
       ($action != "delform") AND ($action != "del")) {
    SetCookie ("current_patient", $id, time()+$_cookie_expire);
    $current_patient = $id;   // patch for first time....
  } // end checking for current_patient value
  else
  {
  	$current_patient=0;
  }

//----- Logon/authenticate
freemed_open_db ();

//---- Push page onto stack
page_push();

switch ($action) {
  case "add": case "addform":
  case "mod": case "modform":
    // addform and modform not used due to "notebook"
   $book = new notebook ( array ("action", "id", "been_here"),
     NOTEBOOK_COMMON_BAR|NOTEBOOK_STRETCH, 3);
   $book->set_submit_name (_("OK"));
   switch ($action) {
     case "add": case "addform":
      if ( !$book->been_here() ) {
        // $ins_disp_inactive=false; // TODO! not implemented
      } // end of checking empty been_here
      $action_name = _("Add");
      break; // end internal add

     case "mod": case "modform":
      if ( !$book->been_here() ) {
      $result = $sql->query("SELECT * FROM patient ".
         "WHERE ( id = '".prepare($id)."' )");

      $r = $sql->fetch_array($result); // dump into array r[]
	  extract($r); // pull variables in from array

        $ptstate      = strtoupper ($ptstate);

        // 19990728 -- next of kin pull and remake
        $ptnextofkin  = htmlentities ($ptnextofkin);

        // resplit email
        if (strlen($ptemail)>3) {
          $ptemail_array = explode ("@", $ptemail);
          $ptemail1      = $ptemail_array[0];
          $ptemail2      = $ptemail_array[1];
        } // end of resplit email
	
        //$ins_disp_inactive=false;
        $been_here = "1"; // set been_here
      } // end of checking empty been_here
      $action_name = _("Modify");
      break; // end internal mod
   } // end of internal switch add/mod

   // ** DISPLAY ADD/MOD ***
   $book->add_page (
     _("Primary Information"),
     array ("ptlname", "ptfname", "ptmname",
            date_vars("ptdob"),
            "ptaddr1", "ptaddr2", "ptcity", "ptstate", "ptzip", "ptcountry",
            "has_insurance"),
		html_form::form_table ( array (
			_("Last Name") =>
				"<INPUT TYPE=TEXT NAME=\"ptlname\" SIZE=25 MAXLENGTH=50 ".
				"VALUE=\"".prepare($ptlname)."\">",
    
			_("First Name") =>
				"<INPUT TYPE=TEXT NAME=\"ptfname\" SIZE=25 MAXLENGTH=50 ".
				"VALUE=\"".prepare($ptfname)."\">",

			_("Middle Name") =>
				"<INPUT TYPE=TEXT NAME=\"ptmname\" SIZE=25 MAXLENGTH=50 ".
				"VALUE=\"".prepare($ptmname)."\">",

			_("Address Line 1") =>
				"<INPUT TYPE=TEXT NAME=\"ptaddr1\" SIZE=25 MAXLENGTH=45 ".
				"VALUE=\"".prepare($ptaddr1)."\">",

			_("Address Line 2") =>
				"<INPUT TYPE=TEXT NAME=\"ptaddr2\" SIZE=25 MAXLENGTH=45 ".
				"VALUE=\"".prepare($ptaddr2)."\">",

			_("City").", "._("State").", "._("Zip") =>
				"<INPUT TYPE=TEXT NAME=\"ptcity\" SIZE=10 MAXLENGTH=45 ".
				"VALUE=\"".prepare($ptcity)."\">\n".
				"<INPUT TYPE=TEXT NAME=\"ptstate\" SIZE=3 MAXLENGTH=2 ".
				"VALUE=\"".prepare($ptstate)."\">\n". 
				"<INPUT TYPE=TEXT NAME=\"ptzip\" SIZE=10 MAXLENGTH=10 ".
				"VALUE=\"".prepare($ptzip)."\">",

			_("Date of Birth") =>
				date_entry("ptdob")


		) )
     );

     $book->add_page(
       _("Contact"),
       array (
         "ptaddr1", "ptaddr2", "ptcity", "ptstate", "ptzip",
	 "ptcountry",
          // phone_vars("pthphone"),
         "pthphone",
         "pthphone_1", "pthphone_2", "pthphone_3", "pthphone_4", "pthphone_5",
          // phone_vars("ptwphone"),
         "ptwphone",
         "ptwphone_1", "ptwphone_2", "ptwphone_3", "ptwphone_4", "ptwphone_5",
	  // phone_vars("ptfax")
         "ptfax",
         "ptfax_1", "ptfax_2", "ptfax_3", "ptfax_4", "ptfax_5",
          // email address portions
         "ptemail1", "ptemail2"
         ),
		html_form::form_table ( array (

			_("Country") =>
				html_form::country_pulldown("ptcountry"),

			_("Home Phone") =>
				fm_phone_entry ("pthphone"),

			_("Work Phone") =>
				fm_phone_entry ("ptwphone"),
    
			_("Fax Number") =>
				fm_phone_entry ("ptfax"),
  
			_("Email Address") =>
				"<INPUT TYPE=TEXT NAME=\"ptemail1\" SIZE=20 MAXLENGTH=40 ".
				"VALUE=\"".prepare($ptemail1)."\"> <B>@</B>\n".
				"<INPUT TYPE=TEXT NAME=\"ptemail2\" SIZE=20 MAXLENGTH=40 ".
				"VALUE=\"".prepare($ptemail2)."\">"

		) )
     );

   	 $ptstatus_r = $sql->query("SELECT ptstatus,ptstatusdescrip,id
                            FROM ptstatus
                            ORDER BY ptstatus");
     $book->add_page(
       _("Personal"),
       array (
         "ptsex", "ptmarital", "ptssn", "ptid",
	 "ptdmv", "ptbilltype", "ptbudg", "ptempl"
	 ),
	html_form::form_table ( array (

		_("Gender") =>
			html_form::select_widget("ptsex",
				array (
					 _("Female")        => "f",
					 _("Male")          => "m",
					 _("Transgendered") => "t"
				)
			),

		_("Marital Status") =>
			html_form::select_widget("ptmarital",
				array (
					_("Single")    => "single",
					_("Married")   => "married",
					_("Divorced")  => "divorced",
					_("Separated") => "separated",
					_("Widowed")   => "widowed"
				)
			),
	
		_("Employment Status") =>
			html_form::select_widget("ptempl",
				array (
					_("Yes")    => "y",
					_("No")     => "n",
					"Part Time" => "p",
					"Self"      => "s",
					"Retired"   => "r",
					"Military"  => "m",
					"Unknown"   => "u"
				)
			),
		_("Patient Status") => 
  			freemed_display_selectbox ($ptstatus_r, "#ptstatus#, #ptstatusdescrip", "ptstatus"),

		_("Social Security Number") =>
			"<INPUT TYPE=TEXT NAME=\"ptssn\" SIZE=9 MAXLENGTH=10 ".
			"VALUE=\"".prepare($ptssn)."\">",

		_("Internal Practice ID #") =>
			"<INPUT TYPE=TEXT NAME=\"ptid\" SIZE=10 MAXLENGTH=10 ".
			"VALUE=\"".prepare($ptid)."\">",
    
		_("Driver's License (No State)") =>
			"<INPUT TYPE=TEXT NAME=ptdmv SIZE=10 MAXLENGTH=9 ".
			"VALUE=\"".prepare($ptdmv)."\">",
       
		_("Type of Billing") =>
			html_form::select_widget("ptbilltype",
				array (
					_("Monthly Billing On Account") => "mon",
					_("Statement Billing")          => "sta",
					_("Charge Card Billing")        => "chg",
					_("NONE SELECTED")              => ""
				)
			),

		_("Monthly Budget Amount") =>
			"<INPUT TYPE=TEXT NAME=ptbudg SIZE=10 MAXLENGTH=20 ".
			"VALUE=\"".prepare($ptbudg)."\">"
		) )

	);


   $ref_phys_r = $sql->query("SELECT phylname,phyfname,id
                            FROM physician WHERE phyref='yes' 
                            ORDER BY phylname,phyfname");
   $int_phys_r = $sql->query("SELECT phylname,phyfname,id
                            FROM physician WHERE phyref='no' 
                            ORDER BY phylname,phyfname");
   $all_phys_r = $sql->query("SELECT phylname,phyfname,id
                            FROM physician
			    ORDER BY phylname,phyfname");

   if (!isset($num_other_docs)) { // first time through
     $num_other_docs=0;
     for ($i=1;$i<=4;$i++) // for ptphy[1..4]
       if (${"ptphy$i"}>0) 
         $num_other_docs++; // some days, i'm so clever it hurts.
   } // is !isset num_other_docs

   $book->add_page(
     _("Physician"),
     array ("ptdoc", "ptphy1", "ptphy2", "ptphy3", "ptphy4", "ptpcp",
            "ptrefdoc", "num_other_docs"),
     "
    <TABLE CELLSPACING=0 CELLPADDING=2 BORDER=0>

    <TR><TD ALIGN=RIGHT>
    "._("In House Doctor")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($int_phys_r, "#phylname#, #phyfname#", "ptdoc")."
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    "._("Referring Doctor")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($ref_phys_r, "#phylname#, #phyfname#", "ptrefdoc")."
    </TD></TR>

    <TR><TD ALIGN=RIGHT>
    "._("Primary Care Physician")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptpcp")."
    </TD></TR>

    ".(($num_other_docs>0) ? "
    <TR><TD ALIGN=RIGHT>
    "._("Other Physician 1")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptphy1")."
    </TD></TR>
    " : "").

    (($num_other_docs>1) ? "
    <TR><TD ALIGN=RIGHT>
    "._("Other Physician 2")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptphy2")."
    </TD></TR>
    " : "").

    (($num_other_docs>2) ? "
    <TR><TD ALIGN=RIGHT>
    "._("Other Physician 3")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($all_phys_r, "#phylname#, #phyfname#", "ptphy3")."
    </TD></TR>
    " : "").

    (($num_other_docs>3) ? "
    <TR><TD ALIGN=RIGHT>
    "._("Other Physician 4")." :
    </TD><TD ALIGN=LEFT>
  ".freemed_display_selectbox ($disp_phys_result, "#phylname#, #phyfname#", "ptphy4")."
    </TD></TR>
    " : "").

    "<TR><TD ALIGN=RIGHT>
    "._("Number of Other Physicians")." :
    </TD><TD ALIGN=LEFT>
      ".html_form::number_pulldown("num_other_docs", 0, 4)."
    </TD></TR>

    </TABLE>
     ");    

   $book->add_page(
     _("Notes"),
     array("ptnextofkin"),
     html_form::form_table(array(
       "" => "
         <CENTER>
         <TEXTAREA NAME=\"ptnextofkin\" ROWS=10 COLS=40>".
	 prepare($ptnextofkin)."</TEXTAREA>
	 </CENTER>
	 "
     ))
   );

   // show notebook
   $page_title = _("Patient")." "._("$action_name");
   if ( ($action=="modform") or ($action=="mod")) {
     $this_patient = new Patient ($id);
     $display_buffer .= freemed_patient_box ($this_patient);
   }

   if (!( $book->is_done() )) {
     $display_buffer .= "<CENTER>\n".$book->display()."</CENTER>\n";
   } else { // if it is done
     switch ($action) {
       case "add": case "addform":
         $ptdtadd = $cur_date; // current date of add...
         $ptdtmod = $cur_date; // current date for mod as well

         // next of kin prepare blob field
         $ptnextofkin = addslashes ($ptnextofkin);

         // assemble phone numbers
         $pthphone   = fm_phone_assemble ("pthphone");
         $ptwphone   = fm_phone_assemble ("ptwphone");
         $ptfax      = fm_phone_assemble ("ptfax");

         // assemble dates
         $ptdob      = fm_date_assemble("ptdob");

         // knock state to upper case
         $ptstate  = strtoupper ($ptstate); 

         // assemble email
         if ((strlen($ptemail1)>0) AND (strlen($ptemail2)>3))
           $ptemail = $ptemail1 . "@" . $ptemail2;
       
         // collapse the TEXT variables...
	 //reset($t_vars);while ($i=next($t_vars)) 
	 //                 $$i = fm_join_from_array($$i);

         $query = "INSERT INTO patient VALUES (
           '$ptdtadd',
           '$ptdtmod',
           '$ptbal',
           '$ptbalfwd',
           '$ptunapp',
           '$ptdoc',
           '$ptrefdoc',
           '$ptpcp',
           '$ptphy1',
           '$ptphy2',
           '$ptphy3',
           '$ptphy4',
           '$ptbilltype',
           '$ptbudg',
           '$ptlname',
           '$ptfname',
           '$ptmname',
           '$ptaddr1',
           '$ptaddr2',
           '$ptcity',
           '$ptstate',
           '$ptzip',
           '$ptcountry',
           '$pthphone',
           '$ptwphone',
           '$ptfax',
           '$ptemail',
           '$ptsex',
           '$ptdob',
           '$ptssn',
           '$ptdmv',
           '$ptdtlpay',
           '$ptamtlpay',
           '$ptpaytype',
           '$ptdtbill',
           '$ptamtbill',
           '$ptstatus',
           '$ptytdchg',
           '$ptar',
           '$ptextinf',
           '$ptdisc',
           '$ptdol',
           '$ptdiag1',
           '$ptdiag2',
           '$ptdiag3',
           '$ptdiag4',
           '$ptid',
           '$pthistbal',
           '$ptmarital',
           '$ptempl',
           '$ptemp1',
           '$ptemp2',
           '$ptguar',
           '$ptrelguar',
           '$ptguarstart',
           '$ptguarend',
           '$ptins',
           '$ptinsno',
           '$ptinsgrp',
           '$ptinsstart',
           '$ptinsend',
           '$ptnextofkin',
           '$__ISO_SET__',
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL,
           NULL) ";
	 break; // end add
       case "mod": case "modform":
         // collapse the TEXT variables...
	 //reset($t_vars);while ($i=next($t_vars)) 
	 //                 if (is_array($$i)) $$i = implode(':', $$i);
         //$ptins{start,end} already fm_date_assemble'd
	 // reassemble email
	 if ((strlen($ptemail1)>1) AND (strlen($ptemail2)>3))
	   $ptemail = $ptemail1 . "@" . $ptemail2;
	 $query = $sql->update_query (
           "patient",
           array (
             "ptdtmod" => date("Y-m-d"),
	     "ptdob" => fm_date_assemble ("ptdob"),
             "ptbal",
             "ptbalfwd",
             "ptunapp",
             "ptrefdoc",
             "ptpcp",
             "ptphy1",
             "ptphy2",
             "ptphy3",
             "ptphy4",
             "ptbilltype",
             "ptbudg",
             "ptdoc",
             "ptlname",
             "ptfname",
             "ptmname",
             "ptaddr1",
             "ptaddr2",
             "ptcity",
             "ptstate" => strtoupper ($ptstate),
             "ptzip",
             "ptcountry",
             "pthphone"  => fm_phone_assemble ("pthphone"),
             "ptwphone"  => fm_phone_assemble ("ptwphone"),
             "ptfax" => fm_phone_assemble ("ptfax"),
             "ptemail",
             "ptsex",
             "ptssn",
             "ptdmv",
             "ptdtlpay",
             "ptamtlpay" => $ptpaytype,
             "ptstatus",
             "ptytdchg",
             "ptar",
             "ptextinf",
             "ptdisc",
             "ptdiag1",
             "ptdiag2",
             "ptdiag3",
             "ptdiag4",
             "ptid",
             "pthistbal",
             "ptmarital",
             "ptempl",
             "ptemp1",
             "ptemp2",
             "ptguar",
             "ptguarstart",
             "ptguarend",
             "ptrelguar",
             "ptins",
             "ptinsno",
             "ptinsgrp",
             "ptinsstart",
             "ptinsend",
             "ptnextofkin",
             "iso"
            ), array ( "id" => $id )
         );
         break; // end mod
     } // end switch for action (done .. actual action)
     $display_buffer .= "
      <CENTER><B>".( (($action=="mod") OR ($action=="modform")) ?
             _("Modifying") : _("Adding") )." ...</B> ";
     $result = $sql->query($query);
     if ($result) $display_buffer .= _("Done");
     else $display_buffer .= _("Error");
     $display_buffer .= "<BR>\n";
	 if ( ($result) AND ($action=="addform") AND (empty($ptid)) )
	 {
		$display_buffer .= "<B>Adding Patient ID ...</B> ";
		$pid = $sql->last_record($result);
		$patid = PATID_PREFIX.$pid;
		$result = $sql->query("UPDATE patient SET ptid='".addslashes($patid)."' ".
			"WHERE id='".addslashes($pid)."'");
     	if ($result) $display_buffer .= _("Done");
     	else $display_buffer .= _("Error");
		$display_buffer .= "<BR>\n";
		
	 }
     $display_buffer .= "
      <P>
      <A HREF=\"manage.php?id=".( $action=="addform" ? $pid : $id )."\">
      "._("Manage This Patient")."
      </A>
      
      </CENTER>
     ";
   } // end checking if done

   break; // end action add/mod

  case "delete":
  case "del":
    $page_title = _("Deleting")." "._($record_name);
    $display_buffer .= "<CENTER>
     <P>"._("Deleting")." ... ";
    $query = "DELETE FROM patient WHERE id='".addslashes($id)."'";
    $result = $sql->query ($query);
    if ($result) { $display_buffer .= _("done")."."; }
     else        { $display_buffer .= _("ERROR");    }
    $display_buffer .= "
     </CENTER>
     <P>
     <CENTER>
     <A HREF=\"patient.php\"
     >"._("back")."</A>
     </CENTER>
    ";
  break; // end action delete

  case "find":
    switch ($criteria) {
      case "letter":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (ptlname LIKE '".addslashes($f1)."%') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = _("Last Names")." (".prepare($f1).")";
        break;
      case "contains":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (".addslashes($f1)." LIKE '%".addslashes($f2)."%') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = _("Searching for")." \"".prepare($f2)."\"";
        break;
      case "soundex":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (soundex(".addslashes($f1).") = soundex('".addslashes($f2)."')) ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "Sounds Like \"".prepare($f2)."\"";
        break;
      case "all":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = "\""._("All Patients")."\"";
        break;
      case "dependants":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (ptdep = '".addslashes($f1)."') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = _("Dependents");
        break;
      case "guarantor":
        $query = "SELECT ptlname,ptfname,ptdob,ptid,id FROM patient ".
         "WHERE (id = '".addslashes($f1)."') ".
         "ORDER BY ptlname, ptfname, ptdob";
        $_crit = _("Guarantor");
        break;
      default:
        $_crit = "";
        break;
    } // end criteria search

    $result = $sql->query($query); 

      $page_title = _("Patients Meeting Criteria")." ".$_crit;

      if (strlen($_ref)<5) {
        $_ref="main.php";
      } // if no ref, then return to home page...

      $display_buffer .= freemed_display_itemlist(
        $result,
	$page_name,
	array (
	  _("Last Name") =>     "ptlname",
	  _("First Name") =>    "ptfname",
	  _("Date of Birth") => "ptdob",
	  _("Practice ID") =>   "ptid"
	),
	array ("","",""),
	"", "", "",
	ITEMLIST_MOD|ITEMLIST_VIEW
      );

      $display_buffer .= "
       <P>
       <CENTER>
        <A HREF=\"$page_name\"
        >"._("back")."</A>
       </CENTER>
       <P>
       ";
      freemed_display_box_bottom (); // display bottom of the box
  break; // end action find
 
  case "display":
  case "view":
    // KludgE AlerTx0r!
    header("Location:".ereg_replace("patient.php",
           "manage.php", $REQUEST_URI));
  break;

  default: // default action
    $page_title = _("Patients");
  
    if (freemed_get_userlevel()>$database_level) {
      $display_buffer .= "
        <TABLE WIDTH=100% BGCOLOR=#000000 BORDER=0 CELLSPACING=0
         CELLPADDING=0 VALIGN=TOP ALIGN=CENTER><TR><TD>
        <FONT FACE=\"Arial, Helvetica, Verdana\" COLOR=#ffffff>
      ";
      $result = $sql->query ("SELECT COUNT(*) FROM patient");
      if ($result) {
        $_res   = $sql->fetch_array ($result);
        $_total = $_res[0];               // total number in db
  
          // patched 19990622 for 1 and 0 values...
        if ($_total>1)
          $display_buffer .= "
            <CENTER>
             <B><I>$_total "._("Patient(s) In System")."</I></B>
            </CENTER>
          ";
        elseif ($_total==0)
          $display_buffer .= "
            <CENTER>
             <B><I>"._("No Patients In System")."</I></B>
            </CENTER>
          ";
        elseif ($_total==1)
          $display_buffer .= "
            <CENTER>
            <B><I>"._("One Patient In System")."</I></B>
            </CENTER>
          ";
      } else {
        $display_buffer .= "
          <CENTER>
           <B><I>"._("No Patients In System")."</I></B>
          </CENTER>
        ";
      } // if there are none...
      $display_buffer .= "
        </FONT>
        </TD></TR></TABLE>
      "; // end table statement for bar
    }

    if ($current_patient>0) {
      $patient = new Patient ($current_patient);
      $display_buffer .= "
        <TABLE WIDTH=100% CELLSPACING=0 CELLPADDING=0 ALIGN=CENTER
         VALIGN=CENTER BORDER=0><TR><TD ALIGN=CENTER><CENTER>
	 <A HREF=\"manage.php?id=$current_patient\"
         >"._("Patient")." : ".$patient->fullName(true)."</A>
         </CENTER></TD></TR></TABLE>
      ";
    } // end check for current patient cookie

    $display_buffer .= "
      <BR>
      <CENTER>
       <B>"._("Patients By Name")."</B>
      <BR>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=A\">A</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=B\">B</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=C\">C</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=D\">D</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=E\">E</A>
  
      <A HREF=\"$page_name?action=find&criteria=letter&f1=F\">F</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=G\">G</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=H\">H</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=I\">I</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=J\">J</A>
  
      <A HREF=\"$page_name?action=find&criteria=letter&f1=K\">K</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=L\">L</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=M\">M</A>
      <BR>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=N\">N</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=O\">O</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=P\">P</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=Q\">Q</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=R\">R</A>
  
      <A HREF=\"$page_name?action=find&criteria=letter&f1=S\">S</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=T\">T</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=U\">U</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=V\">V</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=W\">W</A>
  
      <A HREF=\"$page_name?action=find&criteria=letter&f1=X\">X</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=Y\">Y</A>
      <A HREF=\"$page_name?action=find&criteria=letter&f1=Z\">Z</A>

      <P>

      <FORM ACTION=\"$page_name\" METHOD=POST>
       <B>"._("Patients Field Search")."</B>
      <BR>
      <INPUT TYPE=HIDDEN NAME=\"action\"   VALUE=\"find\">
      <INPUT TYPE=HIDDEN NAME=\"criteria\" VALUE=\"contains\">
      <SELECT NAME=\"f1\">
       <OPTION VALUE=\"ptlname\" SELECTED>"._("Last Name")."
       <OPTION VALUE=\"ptfname\" >"._("First Name")."
       <OPTION VALUE=\"ptdob\"   >"._("Date of Birth")."
       <OPTION VALUE=\"ptid\"    >"._("Internal Practice ID")."
       <OPTION VALUE=\"ptcity\"  >"._("City")."
       <OPTION VALUE=\"ptstate\" >"._("State")."
       <OPTION VALUE=\"ptzip\"   >"._("Zip")."
       <OPTION VALUE=\"pthphone\">"._("Home Phone")."
       <OPTION VALUE=\"ptwphone\">"._("Work Phone")."
       <OPTION VALUE=\"ptemail\" >"._("Email Address")."
       <OPTION VALUE=\"ptssn\"   >"._("Social Security Number")."
       <OPTION VALUE=\"ptdmv\"   >"._("Driver's License")."
      </SELECT>
      <I><FONT SIZE=\"-1\">"._("contains")."</FONT></I>
      <INPUT TYPE=TEXT NAME=\"f2\" SIZE=15 MAXLENGTH=30>
      <INPUT TYPE=SUBMIT VALUE=\"find\">
      </FORM>
      <P>

      <FORM ACTION=\"$page_name\" METHOD=POST>
      <INPUT TYPE=HIDDEN NAME=\"action\" VALUE=\"find\">
      <INPUT TYPE=HIDDEN NAME=\"criteria\" VALUE=\"soundex\">
      <B>"._("Soundalike Search")."</B><BR>
      <SELECT NAME=\"f1\">
       <OPTION VALUE=\"ptlname\" >"._("Last Name")."
       <OPTION VALUE=\"ptfname\" >"._("First Name")."
      </SELECT>
        <I><FONT SIZE=\"-1\">"._("sounds like")."</FONT></I>
      <INPUT TYPE=TEXT NAME=\"f2\" SIZE=15 MAXLENGTH=30>
      <INPUT TYPE=SUBMIT VALUE=\"find\">
      </FORM>
      <P>

      <A HREF=\"$page_name?action=find&criteria=all&f1=\"
       >"._("Show all Patients")."</A> |
      <A HREF=\"$page_name?action=addform\"
       >"._("Add Patient")."</A> |
      <A HREF=\"call-in.php\"
       >"._("Call In Menu")."</A>
      <P> 
      </CENTER>
      <CENTER>
      <A HREF=\"main.php\"
      >"._("Return to Main Menu")."</A>
      </CENTER>
    ";

    break; // end default action
} // end action

freemed_close_db(); 
template_display();
?>


