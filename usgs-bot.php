<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('memory_limit','16M');

require_once( '/home/tparis/Peachy/Init.php' );

$site = Peachy::newWiki( "TPBot" );

$site->set_runpage("User:TPBot/Run/USGS");

//@Param $numOfArticles: Int		Number of article per hour
$numOfArticles = 100;

//GET How many times is the template transcluded
require_once('/home/tparis/database.inc');
	$db = new Database( 'sql-s1-rr', $toolserver_username, $toolserver_password, 'enwiki_p' );

	$num = $db->select(
			'templatelinks',
			'count(*) as count',
			array(
					'tl_title'		=>		'Coord',
					'tl_namespace'		=>		10
		));
//Generate a random number between = and the uBound of transclusion count
$transcount = $num[0]['count'];
$startAt = rand(0, $transcount - $numOfArticles);

//START GENERATING TRANSCLUSIONS

$i = array();
//$i = initPage('Template:Coord')->embeddedin( array( 0 ), $numOfArticles );
$api = $site->apiQuery(array(
		'action'		=>		'query',
		'eititle'		=>		'Template:Coord',
		'prop'			=>		'info',
		'list'			=>		'embeddedin',
		'eicontinue'	=>		'10|Coord|' . $startAt,
		'eilimit'		=>		$numOfArticles
		));

for ($count = 0; $count < count($api['query']['embeddedin']); $count++) {
	$i[$count] = $api['query']['embeddedin'][$count]['title'];
	echo $count . $api['query']['embeddedin'][$count]['title'];
}

//Used for debugging only
//$i[0] = 'User:TParis/usgstext';


foreach ($i as $page) {
	$article = initPage($page);
	$content = $article->get_text();
	$originalcontent = $article->get_text();

	//Make the change
	$makeChange = true;
	$updateArticle = false;

	//Find any {{coor dms}}, {{coor at dm}} or {{coor at dms}} and convert to {{coord}}
	$pattern = "/\{\{(Coor dms|Coor dm| Coor at dm| Coor at dms|coor dms|coor dm|coor at dm|coor at dms)\|(\d{0,2})\|(\d{0,2})\|(N|S)\|(\d{0,2})\|(\d{0,2})\|(E|W)\|([a-zA-Z,=:_\.[:blank:]]*)\|([a-zA-Z0-9,\._=\|[:blank:]]*)\}\}/";
	if (preg_match($pattern, $content)) {
		$replace = "{{coord|$2|$3|$4|$5|$6|$7|$8|$9}}";
		$content = preg_replace($pattern, $replace, $content);
		echo "\nOld template found...\n";
		$updateArticle = true;
	}

	//Check whether a title and inline coord already exists
	$pattern = "/\{\{(?:coord|Coord)([a-zA-Z0-9:=\|_\.]*)display=(title|title,inline|inline,title)([a-zA-Z0-9:=\|_\.[:blank:]]*)?\}\}/";

	if (preg_match($pattern, $content) > 0) {
		echo "\nCoord template with title found...\n";
		$makeChange = false;
	}

	if ($makeChange) {
		$pattern = "/\{\{(?:Coord|coord)([a-zA-Z0-9:=_\|]*)display=inline([a-zA-Z0-9=\|\.[:blank:]]*)?\}\}/";
		$replace = "{{coord$1display=inline,title$2}}";

		echo "\nSetting a title on the first coord template...\n";
		$content = preg_replace($pattern,$replace,$content, 1);
		$updateArticle = true;
	}

	if ($originalcontent == $content) {
		echo "Same!!!";
		$updateArticle = false;
	}

	if ($updateArticle) {
		echo "\nUpdating " . $article->get_title() . "...\n";
		$article->edit($content, "Converting Coord templates, setting format to inline and title", true);
	} else {
		echo "\nNo updates, moving on...\n";
	}

}















