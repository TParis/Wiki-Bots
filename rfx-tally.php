<?php

ini_set('memory_limit','16M');

require_once('/home/tparis/Peachy/Init.php');
 
$wiki = Peachy::newWiki("TPBot");

$wiki->set_runpage("User:TPBot/Run/Tally");

$open_rfxs = initPage("Template:Rfatally")->embeddedin( array( 4 ), 100 );

print_r($open_rfxs);
/*$rfa_main_text = $rfa_main->get_text();
preg_match_all('/\{\{Wikipedia:(.*?)\}\}/', $rfa_main_text, $open_rfxs);
$open_rfxs = $open_rfxs[1];*/

$tallys = array();
foreach( $open_rfxs as $open_rfx ) {
   //$open_rfx = $open_rfx['title'];
   if( in_array( $open_rfx, array( 'Wikipedia:Requests for adminship/Front matter', 'Wikipedia:Requests for adminship/bureaucratship', 'Wikipedia:Requests for adminship' ) ) ) continue;
   if( !preg_match( '/Wikipedia:Requests for (admin|bureaucrat)ship/i', $open_rfx ) ) continue;
   //$open_rfx = str_replace(array('Wikipedia:Requests for adminship/','Wikipedia:Requests for bureaucratship/'),'',$open_rfx);
   
   $myRFA = new RFA( $wiki, $open_rfx);
   
   if ($myRFA->get_lasterror()) {
      die($myRFA->get_lasterror());
   }

   $tally = count($myRFA->get_support()).'/'.count($myRFA->get_oppose()).'/'.count($myRFA->get_neutral());
   
   $open_rfx = str_replace(array('Wikipedia:Requests for adminship/','Wikipedia:Requests for bureaucratship/'),'',$open_rfx);
   $tallys[$open_rfx] = $tally;
}

$out = "{{{{{|safesubst:}}}#switch: {{{1|{{SUBPAGENAME}}}}}\n";

foreach( $tallys as $rfa => $tally ) {
   $out .= "|$rfa= ($tally)\n";
}

$out .= "|#default= (0/0/0)\n}}";

echo $out;

$tally_page = initPage("User:TParis/Tally")->edit($out,"Updating RFA tally");
