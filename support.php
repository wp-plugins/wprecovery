<div class="ocrb-Pro-bootsratp one-click-rollback">
  <div class="container" style="width:100%;">
    <div class="row">
      <div class="col-md-12">
<div class="ocrb-page-bg" style="padding-bottom:20px;">
<?php
/*Controls custom field creation in the dashboard area*/
global $wpdb;
$path =  plugin_dir_url(__FILE__); 
if(isset($_POST['field_submit']))/*Saves the field after clicking save button*/
{
	$message ="";
	if($_POST['field_name']!="")
	$message .= "Name: ".sanitize_text_field($_POST['field_name'])."\r\n";

	if($_POST['field_email']!="")
	$message .= "Email: ".sanitize_email($_POST['field_email'])."\r\n";

	if($_POST['field_phone']!="")
	$message .= "Phone: ".intval($_POST['field_phone'])."\r\n";

	if($_POST['field_issue']!="")
	$message .= "Issue: ".sanitize_text_field($_POST['field_issue'])."\r\n";

	$subject = 'Product Support';
	$to = 'support@cmshelplive.com';
	$headers = 'From: '.sanitize_text_field($_POST['field_name']).' <'.sanitize_email($_POST['field_email']).'>' . "\r\n";
    wp_mail($to,$subject,$message, $headers);
	$result = 'Thank you for contacting us. You will shortly receive your support ticket ID through email you used to send this support request.';
}

?>
<?php if(!empty($result)):?>

<div class="alert-success" style=""><?php echo $result;?></div>
<?php else:?>
<div class="col-md-10 voffset4 col-md-offset-1">
<div class="form-group" style="text-align:center; padding-bottom:20px; border-bottom:1px solid #e5e5e5;">You can directly create a new support ticket on our Helpdesk by using this form. Please allow 10-15 minutes for confirmation of ticket creation. Information will be sent on your email.</div></div>
<div class="col-md-8 voffset1 col-md-offset-2">
<form method="post" action="admin.php?page=ocrb_settings#_support">
  <div class="form-group">
    <label for="field_name">Name</label>
    <input type="text" class="form-control" placeholder="Enter Name" name="field_name" id="field_name" required>
  </div>
  <div class="form-group">
    <label for="exampleInputEmail1">Email Address</label>
    <input type="email" class="form-control" id="exampleInputEmail1" name="field_email" placeholder="Enter email" required>
  </div>
  <div class="form-group">
    <label for="field_phone">Phone Number</label>
    <input type="text" class="form-control" placeholder="Phone Number" name="field_phone" id="field_phone">
  </div>
  <div class="form-group">
    <label for="field_issue">Issue</label>
    <textarea class="form-control" rows="3" placeholder="Issue" name="field_issue" id="field_issue" required></textarea>
  </div>
  <button type="submit" class="btn btn-success" name="field_submit">Submit</button>
  <input type="hidden" name="page" value="ocrb_settings#_support" />
</form>
</div>
<?php endif; ?>
</div>

</div>
</div>
</div>
</div>