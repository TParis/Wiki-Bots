<?php

ini_set('memory_limit','16M');

require_once( '/home/tparis/Peachy/Init.php' );
require_once('/home/tparis/database.inc');

if( defined( 'USESIMPLE' ) ) {
   $site = Peachy::newWiki( "simple" );
   $db = new Database( 'sql-s3-rr', $toolserver_username, $toolserver_password, 'simplewiki_p' );
}
else {
   $site = Peachy::newWiki( "TPBot" );
   $db = new Database( 'sql-s1-rr', $toolserver_username, $toolserver_password, 'enwiki_p' );
}

$site->set_runpage("User:TPBot/Run/Adminstats");

$u = initPage('Template:Adminstats')->embeddedin( array( 2, 3 ) );

$admins = $site->allusers( null, array( 'sysop' ), null, false, array( 'blockinfo', 'groups', 'editcount', 'registration' ), null );

shuffle($u);
// = array("User talk:TParis");

foreach ($u as $name) {
	$issysop = false;
	preg_match("/User( talk)?:([^\/]*)/i", $name, $m);
	foreach( $admins as $admin ) {
		if( $m[2] == $admin['name'] ) {
			$issysop = true;
			print_r($admin);
		}
	}
	if( $issysop ) {
		process($m[2]);	
	}
	else {
		$out = "'''$m[2] is not an administrator.<br>Therefore they have been disallowed the use of adminstats.'''";
		echo $out;
		echo "\n";
		$toedit = "Template:Adminstats/$m[2]";
		initPage( $toedit )->edit($out,"Adminstats are not allowed for this user.",true);
	}
}
function process ($rawuser) {
    global $site, $db;
		$user = initUser( $rawuser );
		$editcount = $user->get_editcount( false, $db );
		$livecount = $user->get_editcount( false, $db, true );
		
		$out = "{{Adminstats/Core\n|edits=$livecount\n|ed=$editcount\n";
		
		$uid = $db->select(
			'user',
			'user_id',
			array(
				'user_name' => $rawuser
			)
		);
		$uid = $uid[0]['user_id'];
		if( !$uid ) return;
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_type' => 'newusers'
			)
		);
		if( !$res ) return;
		
		$out .= "|created={$res[0]['count']}\n";
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'delete'
			)
		);
		if( !$res ) return;
		
		$out .= "|deleted={$res[0]['count']}\n";
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'restore'
			)
		);
		if( !$res ) return;
		
		$out .= "|restored={$res[0]['count']}\n";
		
		 
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'block'
			)
		);
		if( !$res ) return;
		
		$out .= "|blocked={$res[0]['count']}\n";
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'protect'
			)
		);
		if( !$res ) return;
		
		$out .= "|protected={$res[0]['count']}\n";
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'unprotect'
			)
		);
		if( !$res ) return;
		
		$out .= "|unprotected={$res[0]['count']}\n";
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'rights'
			)
		);
		if( !$res ) return;
		
		$out .= "|rights={$res[0]['count']}\n";
		
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'reblock'
			)
		);
		if( !$res ) return;
		
		$out .= "|reblock={$res[0]['count']}\n";
				
		$res = $db->select(
			'logging',
			'count(log_action) AS count',
			array(
				'log_user' => $uid,
				'log_action' => 'modify'
			)
		);
		if( !$res ) return;
		
		$out .= "|modify={$res[0]['count']}\n";
				
		$out .= '|style={{{style|}}}}}';
		echo $out;
		echo "\n";
		$toedit = "Template:Adminstats/$rawuser";
		
		initPage( $toedit )->edit($out,"Updating Admin Stats",true);
}

function toDie($newdata) {
    $f=fopen('./adminstats.log',"a");
              fwrite($f,$newdata);
              fclose($f);  
}

?>