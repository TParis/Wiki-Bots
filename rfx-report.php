<?php

ini_set('memory_limit','16M');

require_once('/home/tparis/Peachy/Init.php');

$wiki = Peachy::newWiki("TPBot");

$wiki->set_runpage("User:TPBot/Run/RfXReport");

echo "Searching for RfX's on the main RfA page...\n\n";
$findrfa = "/\{\{Wikipedia:Requests for adminship\/(.*)\}\}/";
$findrfb = "/\{\{Wikipedia:Requests for bureaucratship\/(.*)\}\}/";

$rfabuffer = initPage( "Wikipedia:Requests for adminship" )->get_text();

$numrfa = 0;
$numrfb = 0;

$out = "<noinclude>{{shortcut|WP:RFXR||WP:BNRX|WP:BN/RfX Report}}</noinclude>\n{| {{{style|align=\"{{{align|right}}}\" cellspacing=\"0\" cellpadding=\"0\" style=\"white-space:wrap; clear: {{{clear|left}}}; margin-top: 0em; margin-bottom: .5em; float: {{{align|right}}};padding: .5em 0em 0em 1.4em; background: none;\"}}}\n|\n{| class=\"wikitable\"\n";

echo "Processing RfA's...\n\n";

preg_match_all($findrfa, $rfabuffer, $matches);
$out .= "! RfA candidate !! S !! O !! N !! S% !! Ending (UTC) !! Time left !! Dups? !! Report";
foreach ($matches[1] as $rfa) {
   if ($rfa != "Front matter" && $rfa != "bureaucratship") {
      $result = processrfx($rfa);
      $out = $out . "\n|-\n" . $result;
      $numrfa++;
      sleep(5);
   }
}

echo "Processing RfB's...\n\n";

preg_match_all($findrfb, $rfabuffer, $matches);
$out .= "\n|-\n! RfB candidate !! S !! O !! N !! S% !! Ending (UTC) !! Time left !! Dups? !! Report";
foreach ($matches[1] as $rfb) {
   $result = processrfx($rfb,"bureaucrat");
   $out = $out . "\n|-\n" . $result;
   $numrfb++;
   sleep(5);
}

$out .= "\n|}\n|}"; //<div align=\"right\">\n<small>''Last updated by '''~~~''' at '''~~~~~'''''</small>\n</div>\n|}\n";

echo "FINAL RESULT: $out\n\n";

echo "Posting results...\n\n";

$output = "User:TParis/RfX Report";


$reportbuffer = initPage( $output )->edit( $out, "Updating RFB Report, $numrfa RFAs, $numrfb RFBs" );

function bailout($message) {
    echo "Fatal Error\n";
    echo "$message\n";
    exit;
}

function countDown($time) {
  // make a unix timestamp for the given date
  $c = $time;
 
  // get current unix timestamp
  $t = time();
 
  // calculate the difference
  $d = ($c - $t);
  if ($d < 0) $d = 0;
 
  $days = floor($d/60/60/24);
  $hours = floor(($d - $days*60*60*24)/60/60);
  $mins = floor(($d - $days*60*60*24 - $hours*60*60)/60);
 
  return array('days' => $days, 'hours' => $hours, 'mins' => $mins);
 
}

function processrfx($candidate, $ship = "admin") {
   global $wiki;
   $ship_initial = strtoupper( $ship[0] );
   
   echo "Loading Request for " . $ship . "ship for $candidate...\n\n";
   $rfabasename = "Wikipedia:Requests for " . $ship . "ship/";
   
   $myRFA = new RFA( $wiki, $rfabasename . $candidate );
   
   $section_support = $myRFA->get_support();
   $section_oppose = $myRFA->get_oppose();
   $section_neutral = $myRFA->get_neutral();

   if ($myRFA->get_lasterror()) {
      bailout($myRFA->get_lasterror());
   }
   
   $enddate = $myRFA->get_enddate();
   $enddate2 = strtotime($enddate);
   $now = time();
   echo "End Date: $enddate... \n\n";
   echo "End Date in Unix time: $enddate2... \n\n";
   echo "Current Unix Time: $now...\n\n";

   if ($now > $enddate2) { 
      echo "$now (current time) is greater than $enddate2 (ending time). \n"; 
      echo "Rf{$ship_initial} is expired.\n\n";
      $s1 = '|expired=yes';
      $timeleft = "Pending closure...";
   } 
   else { 
      echo "$now (current time) is less than $enddate2 (ending time). \n"; 
      echo "Rf{$ship_initial} is not expired.\n\n";
      
      echo "Calculating time left until Rf{$ship_initial} is closed...\n\n";
      $timeleft = countDown($enddate2);
      $timeleft = $timeleft['days']. ' days, ' . $timeleft['hours'] . ' hours';
   }
   
   if( $ship == "bureaucrat" ) {
      $append = "|crat=yes";
   }
   else {
      $append = "|rfa=yes";
   }
   
   $opposes = count($section_oppose);
   $supports = count($section_support);
   $neutrals = count($section_neutral);
   
   $tally = count($supports).'/'.count($opposes).'/'.count($neutrals);
   echo "Supports: $supports...\nOpposes: $opposes...\nNeutrals: $neutrals...\n\n";
   
   echo "Searching for duplicates...\n\n";
   $n_dup = 0;
   foreach($myRFA->get_duplicates() as $dup) {
      $n_dup++;
   }

   if ($n_dup > 0) {
      echo "Duplicate votes were found...\n\n";
      $dups = "'''yes'''";
   }
   else {
      echo "No duplicates found...\n\n";
      $dups = "no";
   }

   return "{{Bureaucrat candidate|candidate= $candidate|support= $supports|oppose= $opposes|neutral= $neutrals|end date= $enddate |time left=$timeleft|dups= $dups" . $s1 . $append . "}}";
}
