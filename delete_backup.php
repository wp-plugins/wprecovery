<?php
global $wpdb;
$id = $_POST['data']['id'];
$ocrb_backup = $wpdb->prefix."ocrb_backup";
$qry = "select file_path from $ocrb_backup where id=".$id;
$path = $wpdb->get_var($qry);
@unlink($path);
$qry = "delete from $ocrb_backup where id=".$id;
$wpdb->query($qry);	
?>