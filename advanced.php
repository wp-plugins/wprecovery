<?php
global $wpdb;
$ocrb_backup =$wpdb->prefix."ocrb_backup";
$qry2 = "select * from $ocrb_backup order by id desc";
$reg2 = $wpdb->get_results($qry2);
function FileSizeConvert($bytes)
{
    $bytes = floatval($bytes);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );
    foreach($arBytes as $arItem)
    {
        if($bytes >= $arItem["VALUE"])
        {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", "," , strval(round($result, 2)))." ".$arItem["UNIT"];
            break;
        }
    }
    return $result;
}
?>
<div id="advanced_rollback_message" class="row" style=" display:none; margin-top:15px;">
  <div class="col-md-4 col-md-offset-4">
  <div class="alert alert-success">Restore Successfull</div>
 </div>
 </div> 
<div class="row">
  <div class="col-md-12">
 <h4 style="margin-top:20px; margin-bottom:0;">Restore from a specific Backup</h4>
 </div>
 </div> 
 <div class="row"> 
  <div class="col-md-12"> 
 
<div class="table-responsive">
<table class="table table-hover">
      <thead>
        <tr>
          <th>#</th>
          <th>Time</th>
          <th>Size</th>
          <th>Event</th>
        </tr>
      </thead>
      <tbody>
      <?php $i=1; foreach($reg2 as $row){ ?>
        <tr>
        <?php 
		if($row->status=='complete' && file_exists($row->file_path)==true):?>
          <td><input type="radio" name="rollbackid" value="<?php echo $row->id;?>"></td>
          <td><?php echo $row->backup_date;?></td>
          <td><?php echo FileSizeConvert(filesize($row->file_path));?></td>
          <td><?php
		  
		  	$details = unserialize($row->details);
			//print_r($details);
			if($row->event_name=='do-core-upgrade') echo 'Wordpress Upgrade';
			
			if(isset($details['plugin']))
			{ 
				$plugin_position = strpos($details['plugin'],'/');
				
				if($plugin_position===false)
				{
					$plugin_position1 = strpos($details['plugin'],'.');
					$plugin = substr($details['plugin'],0,$plugin_position1);
					
				}
				else
				{
					$plugin = substr($details['plugin'],0,$plugin_position);
				}
				$plugin = str_replace('-',' ',$plugin);
				$plugin = str_replace('_',' ',$plugin); 
				
			}
			if(isset($details['plugin']) && $row->event_name!='upgrade-plugin')
			{
				echo $plugin.' Plugin '.$row->event_name; 	
			}
			
		   if($row->event_name=='upgrade-theme')
		   { 
			 $themename = str_replace('_',' ',str_replace('-',' ',$details['theme']));
			 echo $themename. ' Theme Upgrade';
		   }
		   
		   if($row->event_name=='upgrade-plugin')
		   {
		  	 echo $plugin.' Plugin Update';
		   }
		   
		   if(isset($details['stylesheet']))
		   {
		  	 echo $details['stylesheet'].' Theme '.$row->event_name;
		   }
		   
		   ?></td>
	<?php else: ?>
    <?php if($row->status=='processing'){ ?>
    <td></td><td>A new backup is being prepared, a moment please.</td><td></td><td></td>
    <?php } ?>
    
    <?php endif; ?>
        </tr>
        <?php $i++; } ?>
      </tbody>
    </table>
    </div>
    </div>
    </div>
  <div class="right-side-bar-footer" style="display:none;">
          <div class="col-md-3"><button class="btn btn-default" name="rollback" id="advanced_rollback" type="button" style="margin-right: 15px;" onClick="rollback2()">Rollback</button>
          <button id="advanced_rollback_loading" class="btn btn-warning"><span class="glyphicon glyphicon-refresh glyphicon-refresh-animate"></span> Loading...</button>
		  <button type="button" class="btn btn-danger" onClick="confirmdeletebackupmodal()">Delete</button>
          </div>
        </div>
        
        
        <!-- Small modal -->
<div class="modal fade confirm_delete" tabindex="-1" role="dialog" aria-labelledby="DeleteBackup" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="DeleteBackup">Delete Backup</h4>
      </div>
      <div class="modal-body">
        <div>Are you sure you want to delete selected backup?</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-danger" onClick="deletebackup()">Delete</button>
      </div>
      
      
    </div>
  </div>
</div>
<script>
   jQuery("input[name='rollbackid']").click(function () {
    jQuery(".right-side-bar-footer").show(500);
});
function confirmdeletebackupmodal()
{
	jQuery('.confirm_delete').modal('show');	
}
</script>