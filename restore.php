<?php
global $wpdb;
$ocrb_backup =$wpdb->prefix."ocrb_backup";
$qry = "select * from $ocrb_backup order by id desc limit 1,1";
$reg = $wpdb->get_row($qry);
?>
<div id="quick_rollback_message" class="row" style=" display:none; margin-top:15px;">
  <div class="col-md-4 col-md-offset-4">
 <div class="alert alert-success">Restore Successfull</div>
 </div>
 </div>
 
 <?php if(!empty($reg)): ?>
<div class="row ">
  <div class="col-md-12 voffset6 " style="margin-top:0;">
 <h2 class="ocrb-heading">Something went wrong? One click can help!</h2>
 </div>
 </div>
 
 <div class="row">
  <div class="col-md-12 rollback-button voffset2">
 <button name="quick_rollback" id="quick_rollback" type="button" class="btn btn-success" onClick="rollback(<?php if(isset($reg)) echo $reg->id;?>)">Rollback</button>
 <button id="quick_rollback_loading" class="btn btn-warning"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Loading...</button>
 </div>
 </div>
 
 <div class="row">
  <div class="col-md-12 roll-back-information voffset2">
 <p>This button will rollback the site to <?php if(isset($reg)) echo $reg->backup_date; ?></p>
 </div>
 </div>
 
  <div class="row">
  <div class="col-md-12 roll-back-information">
<p><a href="#advanced" data-toggle="tab" onclick="handleTabLinks();">or rollback to another time</a></p>
 </div>
 </div>
 <?php else: ?>
 
 <div id="quick_rollback_message" class="row" style="margin-top:15px;">
  <div class="col-md-11 col-md-offset-1">
 <p class="alert-info col-md-9 col-md-offset-1" style="padding:15px;">You cannot rollback right now. Rollback will become activated once you make any changes to the site.</p>
 </div>
 </div>
 <?php endif; ?>