<?php
  # file: book_appointment.php3
  # note: scheduling module for freemed-project
  # code: jeff b (jeff@univrel.pr.uconn.edu)
  # lic : GPL

  $page_name = "book_appointment.php3";
  include ("global.var.inc");
  include ("freemed-functions.inc");
  include ("freemed-calendar-functions.inc");

  freemed_open_db ($LoginCookie); // authenticate user
  freemed_display_html_top ();
  freemed_display_banner ();

if (strlen($selected_date)!=10) {
  $selected_date = $cur_date;
} // fix date if not correct

// set previous and next date variables...
 $next = freemed_get_date_next ($selected_date);
 $next_wk = $selected_date;
 for ($i=1;$i<=7;$i++) $next_wk = freemed_get_date_next ($next_wk);
 $prev = freemed_get_date_prev ($selected_date);
 $prev_wk = $selected_date;
 for ($i=1;$i<=7;$i++) $prev_wk = freemed_get_date_prev ($prev_wk);

switch ($action) {
 case "":
      // STAGE ONE:

      // BROWSE DATES ON THE CALENDAR TO DECIDE WHERE
      // AND WHAT DAY WE ARE LOOKING FOR...

  freemed_display_box_top ("$Add_Appointment");
  fc_generate_calendar_mini($selected_date,
   "$page_name?$_auth&patient=$patient&room=$room&type=$type");

  //echo "
  //  <CENTER>
  //   <B><FONT FACE=\"Arial, Helvetica, Verdana\">
  //   $Current_Date_is ".fm_date_print($selected_date)."
  //   </FONT></B>
  //  </CENTER>
  //  <BR>
  //";

  if (date_in_the_past($selected_date))
    echo "
      <CENTER><I><$STDFONT_B SIZE=-2
      >$this_date_occurs_in_the_past<$STDFONT_E></I></CENTER>
      <BR>
    ";

    $rm_name = freemed_get_link_field ($room, "room",
      "roomname");
    $rm_desc = freemed_get_link_field ($room, "room",
      "roomdescrip");
    switch ($type) {
      case "temp":
       $ptname = freemed_get_link_rec ($patient, "callin");
       $ptlname = $ptname ["cilname"];
       $ptfname = $ptname ["cifname"];
       $ptmname = $ptname ["cimname"];
       $ptdob   = $ptname ["cidob"  ];
       break;
      case "pat": default:
       $ptname = freemed_get_link_rec ($patient, "patient");
       $ptlname = $ptname ["ptlname"];
       $ptfname = $ptname ["ptfname"];
       $ptmname = $ptname ["ptmname"]; 
       $ptdob   = $ptname ["ptdob"  ];
       break;
    } // end of switch

    if (strlen($rm_desc)<1) { $rm_desc="";               }
     else                   { $rm_desc="(".$rm_desc.")"; }

    if ($debug) $debug_var = "[$room]";

    echo "
      <CENTER>
      <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
       ALIGN=CENTER>
      <TR>
      <TD ALIGN=RIGHT><B>$Patient:</B></TD>
      <TD ALIGN=LEFT>$ptlname, $ptfname $ptmname [".fm_date_print($ptdob).
        " ]</TD></TR>
      ". (
       ($room > 0) ? "
      <TR>
      <TD ALIGN=RIGHT><B>$Room:</B></TD>
      <TD ALIGN=LEFT>$rm_name $rm_desc $debug_var</TD></TR>
        " : "" )."
      <TR>
      <TD ALIGN=RIGHT><B>$Date:</B></TD>
      <TD ALIGN=LEFT>".fm_date_print($selected_date)."</TD></TR>
      </TABLE>
      </CENTER>
    ";
    if (date_in_the_past($selected_date)) 
     echo "
      <BR><CENTER><I><$STDFONT_B SIZE=-2>
      $this_date_occurs_in_the_past<$STDFONT_E></I></CENTER>
     ";
    echo "
      <P><CENTER>
      <FORM ACTION=\"$page_name\" METHOD=POST>
       <INPUT TYPE=HIDDEN NAME=\"action\"
        VALUE=\"\">
       <INPUT TYPE=HIDDEN NAME=\"patient\"
        VALUE=\"".htmlentities($patient)."\">
       <INPUT TYPE=HIDDEN NAME=\"selected_date\"
        VALUE=\"".htmlentities($selected_date)."\">
       <INPUT TYPE=HIDDEN NAME=\"type\"
        VALUE=\"".htmlentities($type)."\">
       <SELECT NAME=\"room\">
    ";
    freemed_display_rooms ($room);
    echo "
       </SELECT>
       <INPUT TYPE=SUBMIT VALUE=\"$Change_Room\">
      </FORM></CENTER>
      <P>
    ";

  //if ($room > 0) {
    // now, find if it is "booked"
    if ($room > 0) { // only if it is specific
        // generate interference map
      fc_generate_interference_map ("calroom='$room'",
         $selected_date);
      if (fc_interference_map_count () < 1) {
        echo "
          <CENTER>
           <I>$The_selected_room_is_free_all_day</I>
          </CENTER>
          <BR>
        ";
      }

      // display calendar here

      echo "
        <TABLE WIDTH=100% BORDER=1 CELLSPACING=0 CELLPADDING=3
         BGCOLOR=#777777><TR>
        <TD COLSPAN=2><CENTER>
         <$STDFONT_B SIZE=-1
          COLOR=#ffffff><B>$TIME</B><$STDFONT_E></CENTER></TD>
      ";

      $_alternate = freemed_bar_alternate_color ();
      for ($i=fc_starting_hour();$i<=fc_ending_hour();$i++) {
        $_alternate = freemed_bar_alternate_color ($_alternate);
        if ($i > 11) { 
          $ampm = "pm"; 
          if ($i>12) $ampm_t = $i - 12;
            elseif ($i==12) $ampm_t=$i;
        } else { $ampm = "am"; $ampm_t = $i;}
        if (!fc_check_interference_map($i, "0", $selected_date, false) or
            (freemed_config_value("cal_ob")=="enable")) {
          echo "
            <TR BGCOLOR=$_alternate>
            <TD ALIGN=RIGHT VALIGN=TOP>
            <$STDFONT_B>
            <A HREF=\"$page_name?$_auth&action=step2&patient=$patient&hour=$i".
            "&minute=00&room=$room&selected_date=$selected_date&type=$type\"
            >$ampm_t $ampm</A><$STDFONT_E></TD><TD ALIGN=CENTER>
          ";
        } else { // if we _can't_ book here
          $interfere = fc_check_interference_map ($i, "0", $selected_date,
             false);
          echo "
            <TR BGCOLOR=$_alternate>
            <TD ALIGN=RIGHT VALIGN=TOP>
            <$STDFONT_B>
           ";
          if ($interfere) echo "<I>";
          echo "$ampm_t $ampm";
          if ($interfere) echo "</I>";
          echo "
            <$STDFONT_E></TD><TD ALIGN=CENTER>
          ";
        } // end checking if booked

        for ($j=15;$j<=45;$j+=15) {
          if (!fc_check_interference_map($i, $j, $selected_date, false) or
              freemed_config_value("cal_ob")=="enable") {
            echo "
             <$STDFONT_B>
             <A HREF=\"$page_name?$_auth&action=step2&patient=$patient&".
             "hour=$i&minute=$j&room=$room&selected_date=$selected_date&".
             "type=$type\"
             ><B>:$j</B></A><$STDFONT_E>&nbsp;
            ";
          } else {
            $interfere = fc_check_interference_map($i, $j, $selected_date,
               false);
            echo "
             <$STDFONT_B>
             ";
            if ($interfere) echo "<I>";
            echo "<B>:$j</B>";
            if ($interfere) echo "</I>";
            echo "<$STDFONT_E>&nbsp;\n";
          } // end checking for booked?
        } // end for minutes loop

        echo "
          </TD></TR>
        "; // end row
      } // end for loop (hours)
      echo "
        </TABLE>
      ";
    } // why is this here?

    if ($type=="pat") {
      echo "
        <P>
        <CENTER><A HREF=\"manage.php3?$_auth&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></CENTER>
        <P>
      ";
    } else {
      echo "
        <P>
        <CENTER><A HREF=\"call-in.php3?$_auth&action=view&id=$patient\"
         ><$STDFONT_B>$Manage_Patient<$STDFONT_E></CENTER>
        <P>
      ";
    } // end checking for type

  //} // end if...else for room (whether > 1 or not)

  freemed_display_box_bottom ();
  break; 
 case "step2":

      // STAGE TWO:

      // ACTUALLY BOOKING SOMETHING... REQUIRES ROOM, HOUR,
      // PATIENT NUMBER, PHYSICIAN, ETC... THIS IS THE
      // FINAL FORM.

   freemed_display_box_top ("$Add_Appointment", $_ref);

   if (strlen($room)>0) {
     $rm_name = freemed_get_link_field ($room, "room",
       "roomname");
     $rm_desc = freemed_get_link_field ($room, "room",
       "roomdescrip");

     if (strlen($rm_desc)<1) $rm_desc="";
     else $rm_desc="(".$rm_desc.")";
   } else {
     $rm_name = "$NO_PREFERENCE";
     $rm_desc = "";
   } // checking if room

   switch ($type) {
    case "temp":
     $pt_lname = freemed_get_link_field ($patient, "callin",
       "cilname");
     $pt_fname = freemed_get_link_field ($patient, "callin",
       "cifname");
     break;
    case "pat":
    default:
     $pt_lname = freemed_get_link_field ($patient, "patient",
       "ptlname");
     $pt_fname = freemed_get_link_field ($patient, "patient",
       "ptfname");
   }

   if ($hour > 11) { 
     $ampm = "pm"; 
     if ($hour>12) $ampm_t = $hour - 12;
       elseif ($hour==12) $ampm_t=12;
   } else { $ampm = "am"; $ampm_t = $hour;}
   
     // find default physician by room, if there is one
   if ($room!=0)
     if (freemed_get_link_field($room, "room", "roomdefphy")!=0)
       $physician = freemed_get_link_field($room, "room", "roomdefphy");

     // find the facility for it, with info
   $facility = freemed_get_link_field ($room, "room", "roompos");
   if ($facility > 0) {
     $fac_name = freemed_get_link_field ($facility, "facility", "psrname");
   } else {
     $fac_name = "Default Facility";
   } // end checking for facility

   if ($debug) $debug_var = "[$room]";
   echo "
     <FORM ACTION=\"$page_name\">
     <INPUT TYPE=HIDDEN NAME=\"action\"   VALUE=\"add\">
     <INPUT TYPE=HIDDEN NAME=\"patient\"  VALUE=\"$patient\">
     <INPUT TYPE=HIDDEN NAME=\"room\"     VALUE=\"$room\">
     <INPUT TYPE=HIDDEN NAME=\"facility\" VALUE=\"$facility\">
     <INPUT TYPE=HIDDEN NAME=\"type\"     VALUE=\"$type\">
     <INPUT TYPE=HIDDEN NAME=\"selected_date\" VALUE=\"$selected_date\">
     <INPUT TYPE=HIDDEN NAME=\"hour\"     VALUE=\"$hour\">
     <INPUT TYPE=HIDDEN NAME=\"minute\"   VALUE=\"$minute\">

     <TABLE BORDER=0 CELLSPACING=2 CELLPADDING=2 VALIGN=MIDDLE
      ALIGN=MIDDLE>

     <TR>
     <TD ALIGN=RIGHT><B>$Facility</B>:</TD>
     <TD ALIGN=LEFT>$fac_name</TD></TR>
     <TR>
     <TD ALIGN=RIGHT><B>$Room</B>:</TD>
     <TD ALIGN=LEFT>$rm_name $rm_desc</TD></TR>
     <TR>
     <TD ALIGN=RIGHT><B>$Patient</B>:</TD>
     <TD ALIGN=LEFT>$pt_lname, $pt_fname</TD></TR>
     <TR>
     <TD ALIGN=RIGHT><B>$Date</B>:</TD>
     <TD ALIGN=LEFT>".fm_date_print($selected_date)."</TD></TR>
     <TR>
     <TD ALIGN=RIGHT><B>$Time</B>:</TD>
     <TD ALIGN=LEFT>$ampm_t $minute $ampm</TD></TR>

     <TR>
     <TD ALIGN=RIGHT><B>$Duration</B>:</TD>
     <TD ALIGN=LEFT><SELECT NAME=\"duration\">
       <OPTION VALUE=\"15\" >15 $lang_min_abbrev
       <OPTION VALUE=\"30\" >30 $lang_min_abbrev
       <OPTION VALUE=\"45\" >45 $lang_min_abbrev
       <OPTION VALUE=\"60\" >1 $lang_hour
       <OPTION VALUE=\"75\" >1$lang_h 15$lang_m
       <OPTION VALUE=\"90\" >1$lang_h 30$lang_m
       <OPTION VALUE=\"105\">1$lang_h 45$lang_m
       <OPTION VALUE=\"120\">2 $lang_hours
       <OPTION VALUE=\"180\">3 $lang_hours
      </SELECT></TD></TR>

     <TR>
     <TD ALIGN=RIGHT><B>$Physician</B>:</TD>
     <TD ALIGN=LEFT><SELECT NAME=\"physician\">
   ";

   freemed_display_physicians ($physician);

   echo "
      </SELECT></TD></TR>

     <TR>
     <TD ALIGN=RIGHT><B>$Note</B>:</TD>
     <TD ALIGN=LEFT><INPUT TYPE=TEXT NAME=\"note\" VALUE=\"\"
      SIZE=40 MAXLENGTH=100></TD></TR>
     </TABLE>

     <BR>
     <CENTER>
      <INPUT TYPE=SUBMIT VALUE=\" Commit Booking \">
     </CENTER>
     </FORM>
   ";
   freemed_display_box_bottom ();
  break;
 case "add":
  freemed_display_box_top ("$Adding_Appointment", $_ref);
  echo "$Adding... ";
  $query = "INSERT INTO scheduler VALUES (
    '$selected_date',
    '$type',
    '$hour',
    '$minute',
    '$duration',
    '$facility',
    '$room',     
    '$physician',
    '$patient',
    '$cptcode',
    '$status',
    '".addslashes($note)."',
    '',
    NULL )";
  $result = fdb_query ($query);

  if ($debug) {
    echo "
      <BR>
      <B>RESULT</B>: $result
      <BR>
      <B>QUERY</B>: $query
      <BR>
    ";
  } // end debug...
  echo "
    done.
    <BR>
    <CENTER>
  ";
  if ($type=="pat") {
    echo "
     <A HREF=\"manage.php3?$_auth&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
     </CENTER>
    ";
  } else {
    echo "
     <A HREF=\"call-in.php3?$_auth&action=display&id=$patient\"
     ><$STDFONT_B>$Manage_Patient<$STDFONT_E></A>
     </CENTER>
    ";
  } // end checking type

  freemed_display_box_bottom ();
  break;
} // end master switch

  freemed_close_db (); // close the db
  freemed_display_html_bottom (); // show bottom of HTML code

?>
