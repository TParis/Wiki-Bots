<?php

ini_set('memory_limit','16M');

require_once( '/home/tparis/Peachy/Init.php' );

$site = Peachy::newWiki( "TPBot" );

$site->set_runpage("User:TPBot/Run/BIL");

//START GENERATING TRANSCLUSIONS
$i = array();
$i = initPage('Template:Badimage')->embeddedin( array( 6, 7 ) );

//END GENERATING TRANSCLUSIONS

//START GENERATING BAD IMAGE LIST
$bil = initPage('MediaWiki:Bad image list')->get_text();
preg_match_all('/\*\s\[\[\:(File\:(.*?))\]\]/i', $bil, $bad_images);
$bad_images = $bad_images[1];
print_r($bad_images);
//END GENERATING BAD IMAGE LIST

//START PROCESSING EACH IMAGE
foreach( $i as $image ) {
   if( in_array( str_replace('File talk','File',$image), $bad_images ) ) {
      continue;
   }
   else {
      $image_page_object = initPage($image);

      $image_page = $image_page_object->get_text();
      $new_image_page = str_ireplace('{{badimage}}','',$image_page);
      //echo getTextDiff('unified', $image_page, $new_image_page);
      
      if( $image_page == $new_image_page ) continue;
      
      $image_page_object->edit($new_image_page,"Removing {{badimage}}, image is not on blacklist",true);
      continue;
   }
   
   if( str_replace('File talk','File',$image) != $image ) {
      $image_page_object = initPage(str_replace('File talk','File',$image));
      $image_page = $image_page_object->get_text();

      $image_talk_page_object = initPage($image);
      $image_talk_page = $image_talk_page_object->get_text();

      //START REMOVAL FROM TALK PAGE
      $new_image_talk_page = str_ireplace('{{badimage}}','',$image_talk_page);
      //echo getTextDiff('unified', $image_talk_page, $new_image_talk_page);
      
      if( $image_talk_page == $new_image_talk_page ) continue;
      $image_talk_page_object->edit($new_image_talk_page,"Removing {{badimage}}, moving to main image page",true);
      
      //START ADDITION TO MAIN PAGE
      if( preg_match('/\{\{badimage/i', $image_page ) ) continue;
      $new_image_page = "{{badimage}}\n$image_page";
      //echo getTextDiff('unified', $image_page, $new_image_page);
      
      if( $image_page == $new_image_page ) continue;
      
      $image_page_object->edit($new_image_page,"Adding {{badimage}}",true);
   }
}
//END PROCESSING EACH IMAGE

//START GENERATING TRANSCLUSIONS
$i = array();
$i = initPage('Template:Badimage')->embeddedin( array( 6, 7 ) );

//END GENERATING TRANSCLUSIONS

//START GENERATING BAD IMAGE LIST
$bil = initPage('MediaWiki:Bad image list')->get_text();
preg_match_all('/\*\s\[\[\:(File\:(.*?))\]\]/i', $bil, $bad_images);
$bad_images = $bad_images[1];
print_r($bad_images);
//END GENERATING BAD IMAGE LIST

//START GOING THROUGH BIL
foreach( $bad_images as $bad_image ) {
   $image = initPage($bad_image);
   
   $image_page = $image->get_text();
   if( preg_match('/\{\{badimage/i', $image_page ) ) continue;
   
   $new_image_page = "{{badimage}}\n$image_page";
   //echo getTextDiff('unified', $image_page, $new_image_page);
      
   if( $image_page == $new_image_page ) continue;
   
   $image->edit($new_image_page,"Adding {{badimage}}",true);

}
//END GOING THROUGH BIL

