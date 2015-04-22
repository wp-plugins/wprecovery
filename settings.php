<?php

$plugin_dir_url =  plugin_dir_url(__FILE__); 

?>

<div class="ocrb-Pro-bootsratp one-click-rollback">
  <div class="container" style="width:100%;">
    <div class="row">
      <div class="col-md-2 side-bar ">
        <ul id="settingmaintab" class="nav nav-pills nav-stacked ">
          <li class="active"><a href="#restore" data-toggle="tab" onclick="handleTabLinks();"><span class="dashicons dashicons-backup"></span>Restore</a></li>
          <li style="display:none;"><a href="#advanced" data-toggle="tab" onclick="handleTabLinks();"><span class="dashicons dashicons-menu"></span>Advanced</a></li>
          <li><a href="#support" data-toggle="tab" onclick="handleTabLinks();"><span class="dashicons dashicons-admin-generic"></span>Support</a></li>
        </ul>
        <!--<div class="left-side-bar-footer">
          <div class="col-md-5"> <img src="<?php //echo $plugin_dir_url; ?>images/rating.png"/><span>Rate </span></div>
          <div class="col-md-7"><img src="<?php //echo $plugin_dir_url; ?>images/support-icon.png"/><span>Support </span> </div>
        </div>-->
      </div>
      <div class="col-md-10 page-wrapper">
        <div id="page-wrapper">
          <div class="tab-content"> 
            
            <!-- Left panell Settings tab3 -->
            
            <div class="tab-pane active" id="restore">
              <div class="ocrb-page-bg">
                <?php include 'restore.php';?>
              </div>
            </div>
            
            <!-- End Left panell Settings tab3 --> 
            
            <!-- Left panell Profile Fields tab3 -->
            
            <div class="tab-pane" id="advanced">
              <div class="ocrb-page-bg">
                <?php include 'advanced.php';?>
              </div>
            </div>
            <div class="tab-pane" id="support">
              <div class="ocrb-page-bg">
              <div class="row">
  				<div class="col-md-12">
                <?php include 'support.php';?>
                </div>
                </div>
              </div>
            </div>
            
            <!-- End panell Profile Fields tab3 --> 
            
          </div>
          
          <!-- /.container-fluid --> 
          
        </div>
      </div>
    </div>
    
  </div>
</div> 
 <script>
    handleTabLinks();
    function handleTabLinks() {
    if(window.location.hash == '') {
        window.location.hash = window.location.hash + '#_';
    }
    var hash = window.location.hash.split('#')[1];
    var prefix = '_';
    var hpieces = hash.split('/');
    for (var i=0;i<hpieces.length;i++) {
        var domelid = hpieces[i].replace(prefix,'');
        var domitem = jQuery('a[href=#' + domelid + '][data-toggle=tab]');
        if (domitem.length > 0) {
            domitem.tab('show');
        }
    }
    jQuery('a[data-toggle=tab]').on('click', function (e) {
        if (jQuery(this).hasClass('nested')) {
            var nested = window.location.hash.split('/');
            window.location.hash = nested[0] + '/' + e.target.hash.split('#')[1];
        } else {
            window.location.hash = e.target.hash.replace('#', '#' + prefix);
        }
    });
}
</script> 

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