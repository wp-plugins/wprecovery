<?php

$plugin_dir_url =  plugin_dir_url(__FILE__); 

?>

<div class="ocrb-Pro-bootsratp one-click-rollback">
  <div class="container" style="width:100%;">
    <div class="row">
      <div class="col-md-12">
        <div class="ocrb-page-bg">
                <?php include 'restore.php';?>
        </div>
        
      </div>
    </div>
   
  </div>
</div> 
 
<script>
ajax_url = "<?php echo admin_url( 'admin-ajax.php' ); ?>";

function rollback(id)
{
		jQuery('#quick_rollback_loading').show();
		jQuery('#quick_rollback').hide();
        jQuery.post( ajax_url, 
           { 'action': 'rollback','data': { id: id}},

            function (data) {
				jQuery('#quick_rollback_loading').hide();
				jQuery('#quick_rollback').show();
				jQuery('#quick_rollback_message').show();
            });
}

function rollback2()
{
		jQuery('#advanced_rollback_loading').show();
		jQuery('#advanced_rollback').hide();
		id = jQuery('input[name=rollbackid]:checked').val();

        jQuery.post( ajax_url, 
           { 'action': 'rollback','data': { id: id}},
		
            function (data) {
              jQuery('#advanced_rollback_loading').hide();
				jQuery('#advanced_rollback').show();
				jQuery('#advanced_rollback_message').show();
            });
}

function deletebackup()
{
		id = jQuery('input[name=rollbackid]:checked').val();

        jQuery.post( ajax_url, 
           { 'action': 'delete_ocrb_backup','data': { id: id}},
           
            function (data) {
				location.reload();
            });
}
</script>