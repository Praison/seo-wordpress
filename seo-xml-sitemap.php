<?php


//add_action( 'save_post', 'producers_xml' );

function producers_xml(){

 if ($_POST['post_type'] == 'producer') 
 {
   $xml = new SimpleXMLElement('<xml/>');
   $producers = get_posts( array( 'post_type'=>'producer', 'numberposts'=>-1 ) );

   $xml->addChild('producers');

   foreach($producers as $i=>$producer){
     $name = get_the_title($producer->ID);
     $owner = get_post_meta($producer->ID, '_producer_contact_name', true);
     $phone = get_post_meta($producer->ID, '_producer_phone', true);
     $fax = get_post_meta($producer->ID, '_producer_fax', true);
     $email = get_post_meta($producer->ID, '_producer_email', true);
     $website = get_post_meta($producer->ID, '_producer_website', true);
     $address = get_post_meta($producer->ID, '_producer_address', true);

     $xml->producers->addChild('producer');
     $xml->producers->producer[$i]->addChild('name', $name);
     $xml->producers->producer[$i]->addChild('owner', $owner);
     $xml->producers->producer[$i]->addChild('phone', $phone);
     $xml->producers->producer[$i]->addChild('fax', $fax);
     $xml->producers->producer[$i]->addChild('email', $email);
     $xml->producers->producer[$i]->addChild('website', $website);
     $xml->producers->producer[$i]->addChild('address', $address); 
 }

  $file = 'home/my/path/uploads/producers.xml'; //Absolute path
if(is_file($file) && is_readable($file)){
 $open = fopen($file, 'w') or die ("File cannot be opened.");
 fwrite($open, $xml->asXML());
 fclose($open);
}
}
}
?>