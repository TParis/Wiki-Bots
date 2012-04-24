<?php

ini_set('memory_limit','16M');

require_once( '/home/tparis/Peachy/Init.php' );

$site = Peachy::newWiki( "TPBot" );

$site->set_runpage("User:TPBot/Run/USGS");

//START GENERATING TRANSCLUSIONS
$i = array();
//$i = initPage('Template:Coord')->embeddedin( array( 0 ) );


//shuffle($i);

$i[0] = initPage('User:TParis/usgstext');

$intCount = 0;

foreach ($i as $page) {
	$content = $page->get_text();

	//Make the change
	$makeChange = true;

	//Find any {{coor dms}}, {{coor at dm}} or {{coor at dms}} and convert to {{coord}}
	$pattern = "/\{\{(coor dms|coor dm|coor at dm|coor at dms)\|(\d*)\|(\d*)\|(N|S)\|(\d*)\|(\d*)\|(E|W)\|([a-zA-Z:_\.]*)\|(.*)\}\}/";
	$replace = "{{coord|$2|$3|$4|$5|$6|$7|$8|$9}}";
	$content = preg_replace($pattern, $replace, $content);


	//Check whether a title and inline coord already exists
	$pattern = "/\{\{coord(\|.*)*display=(title|title,inline|inline,title)\|(.*)\}\}/";

	if (preg_match($pattern, $content) > 0) {
		$makeChange = false;
	}

	echo "\n" . preg_replace('/\{\{coord(\|.*)*display=inline\|(.*)\}\}/','{{coord$1display=inline,title|$2}}',$content) . "\n";

	if ($makeChange) {
		$pattern = "/\{\{coord(\|.*)*display=inline\|(.*)\}\}/";
		$replace = "{{coord$1display=inline,title|$2}}";
		$content = preg_replace($pattern,$replace,$content, 1);

	}

	$page->edit($content, "Converting Coord templates, setting format to inline and title", true);

	$intCount++
	
	if ($intCount > 60) {
		break;
	}

}















