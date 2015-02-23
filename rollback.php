<?php
global $wpdb;
$id = $_POST['data']['id'];
$ocrb_backup = $wpdb->prefix."ocrb_backup";
$qry = $wpdb->prepare("select file_path from $ocrb_backup where id= %d",$id);
$path = $wpdb->get_var($qry);
$restore_class = new Wpbp_Restore;
$status = $restore_class->start($id,$path); 

$qry2 = "delete from $ocrb_backup where status = 'processing'";
$wpdb->query($qry2);	

 ?>