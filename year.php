<?php
include_once 'includes/init.php';
send_no_cache_header ();

if (($user != $login) && $is_nonuser_admin)
  load_user_layers ($user);
else
  load_user_layers ();

function display_small_month ( $thismonth, $thisyear, $showyear ) {
  global $WEEK_START, $user, $login, $boldDays, $get_unapproved, $friendly;

  if ( $user != $login && ! empty ( $user ) )
    $u_url = "&amp;user=$user";
  else
    $u_url = "";

  //start the minical table for each month
  echo "\n<table class=\"minical\">\n";
	$monthstart = mktime(2,0,0,$thismonth,1,$thisyear);
	$monthend = mktime(2,0,0,$thismonth + 1,0,$thisyear);

	//print the month name
	//if not in printer-friendly mode, also link it to month view
	echo "<caption>";
		if ( empty ( $friendly ) ) {
			echo "<a href=\"month.php?year=$thisyear&amp;month=$thismonth"
				. $u_url . "\">";
		}
		echo month_name ( $thismonth - 1 ) .
			( empty ( $friendly ) ? "</a>" : "" ) . 
	"</caption>\n";
	//determine if the week starts on sunday or monday
	if ( $WEEK_START == "1" ) {
		$wkstart = get_monday_before ( $thisyear, $thismonth, 1 );
	} else {
		$wkstart = get_sunday_before ( $thisyear, $thismonth, 1 );
	}
	
	//print the headers to display the day of the week (sun, mon, tues, etc.)
	echo "<tr class=\"day\">\n";
		//if the week doesn't start on monday, print the day
		if ( $WEEK_START == 0 ) echo "<th>" .
			weekday_short_name ( 0 ) . 
		"</th>\n";
		//cycle through each day of the week until gone
		for ( $i = 1; $i < 7; $i++ ) {
			echo "<th>" .
				weekday_short_name ( $i ) . 
			"</th>\n";
		}
		//if the week DOES start on monday, print sunday
		if ( $WEEK_START == 1 ) echo "<th>" .
			weekday_short_name ( 0 ) . 
		"</th>\n";
	//end the header row
	echo "</tr>\n";
  for ($i = $wkstart; date("Ymd",$i) <= date ("Ymd",$monthend); //for each week of the month...
    $i += (24 * 3600 * 7) ) {
		echo "<tr class=\"numdate\">\n"; //..print this
     for ($j = 0; $j < 7; $j++) {
       $date = $i + ($j * 24 * 3600);
       $dateYmd = date ( "Ymd", $date );
      $hasEvents = false; //presume no events exist, until proven otherwise
      if ( $boldDays ) { //if the user has enabled the option to distinguish days with events...
        $ev = get_entries ( $user, $dateYmd, $get_unapproved );
        if ( count ( $ev ) > 0 ) { //..determine if there are events
          $hasEvents = true; //there are events
        } else {
          $rep = get_repeating_entries ( $user, $dateYmd, $get_unapproved );
          if ( count ( $rep ) > 0 ) //..also determine if we have repeating events
            $hasEvents = true; //there are repeating events
        }
      }
	if ( $dateYmd >= date ("Ymd",$monthstart) &&
          $dateYmd <= date ("Ymd",$monthend) ) { //if there are still days left in the month...
            echo "<td"; //..start the cell containing each date
		$wday = date ( "w", $date );
		$class = array();  // array of classes this cell belongs to
		if ( $wday == 0 || $wday == 6 ) $class[] = "weekend";
		if ( $hasEvents ) $class[] = "hasevents";
			$class = implode ( " ", $class ); // turn the array into a string
		if ( strlen ( $class ) ) echo " class=\"$class\"";
		if ( $dateYmd == date ( "Ymd" ) )
			echo " id=\"today\"";
			echo ">";
		if ( empty ( $friendly ) )
			echo "<a href=\"day.php?date=" .  $dateYmd . $u_url .  "\">";
		echo date ( "d", $date );
		if ( empty ( $friendly ) )
			echo "</a>";
		echo "</td>\n";
		} else {
			echo "<td class=\"empty\">&nbsp;</td>\n";
		}
	}                 // end for $j
echo "</tr>\n";
}                         // end for $i
echo "</table>\n"; //end this minicalendar
}

if ( empty ( $year ) )
  $year = date("Y");

$thisyear = $year;
if ( $year != date ( "Y") )
  $thismonth = 1;

if ( $year > "1903" )
  $prevYear = $year - 1;
else
  $prevYear=$year;

$nextYear= $year + 1;

if ( $allow_view_other != "Y" && ! $is_admin )
  $user = "";

$boldDays = false;
if ( ! empty ( $bold_days_in_year ) && $bold_days_in_year == 'Y' ) {
  /* Pre-Load the repeated events for quckier access */
  $repeated_events = read_repeated_events (
    ( ! empty ( $user ) && strlen ( $user ) ) ? $user : $login, $cat_id, $year . "0101" );

  /* Pre-load the non-repeating events for quicker access */
  $events = read_events ( ( ! empty ( $user ) && strlen ( $user ) )
    ? $user : $login, $year . "0101", $year . "1231", $cat_id );
  $boldDays = true;
}

// Include unapproved events?
$get_unapproved = ( $DISPLAY_UNAPPROVED == 'Y' );
if ( $user == "__public__" )
  $get_unapproved = false;

 print_header();
 ?>
 
<div class="title">
	<?php if ( empty ( $friendly ) ) { ?>
		<a title="<?php etranslate("Previous")?>" class="prev" href="year.php?year=<?php echo $prevYear; if ( ! empty ( $user ) ) echo "&amp;user=$user";?>"><img src="leftarrow.gif" alt="<?php etranslate("Previous")?>" /></a>
		<a title="<?php etranslate("Next")?>" class="next" href="year.php?year=<?php echo $nextYear; if ( ! empty ( $user ) ) echo "&amp;user=$user";?>"><img src="rightarrow.gif" alt="<?php etranslate("Next")?>" /></a>
	<?php } ?>
	<span class="date"><?php echo $thisyear ?></span>
	<span class="user"><?php
		if ( $single_user == "N" ) {
			echo "<br />\n";
			if ( ! empty ( $user ) ) {
				user_load_variables ( $user, "user_" );
				echo $user_fullname;
			} else {
				echo $fullname;
			}
			if ( $is_assistant )
				echo "<br /><span style=\"font-weight:bold;\">-- " . translate("Assistant mode") . " --</span>";
		}
	?></span>
</div>
<br />
 
<div align="center">
	<table class="main">
		<tr><td>
			<?php display_small_month(1,$year,False); ?></td><td>
			<?php display_small_month(2,$year,False); ?></td><td>
			<?php display_small_month(3,$year,False); ?></td><td>
			<?php display_small_month(4,$year,False); ?>
		</td></tr>
		<tr><td>
			<?php display_small_month(5,$year,False); ?></td><td>
			<?php display_small_month(6,$year,False); ?></td><td>
			<?php display_small_month(7,$year,False); ?></td><td>
			<?php display_small_month(8,$year,False); ?>
		</td></tr>
		<tr><td>
			<?php display_small_month(9,$year,False); ?></td><td>
			<?php display_small_month(10,$year,False); ?></td><td>
			<?php display_small_month(11,$year,False); ?></td><td>
			<?php display_small_month(12,$year,False); ?>
		</td></tr>
	</table>
</div>

<br />
<?php if ( empty ( $friendly ) ) {
	display_unapproved_events ( $login );
?>
<br />
<a title="<?php etranslate("Generate printer-friendly version")?>" class="printer" href="year.php?<?php
  if ( $thisyear )
    echo "year=$thisyear&amp;";
  if ( $user != $login && ! empty ( $user ) )
    echo "user=$user&amp;";
?>friendly=1" target="cal_printer_friendly"
onmouseover="window.status = '<?php etranslate("Generate printer-friendly version")?>'">[<?php etranslate("Printer Friendly")?>]</a>

<?php }
print_trailer();
?>
</body>
</html>
