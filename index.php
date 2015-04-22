<?php
/*
Plugin Name: WP Recovery
Plugin URI: http://cmshelplive.com
Description: Restore Your Site If It Ever Stops Working Correctly
Author: CMSHelpLive
Version: 2.0
Author URI: http://cmshelplive.com/
License: gpl2
*/
register_activation_hook ( __FILE__, 'activate_one_click_rollback_fun' );
register_deactivation_hook (__FILE__, 'deactivate_one_click_rollback_fun' );
function deactivate_one_click_rollback_fun()
{
	wp_clear_scheduled_hook( 'one_click_backup' );
	global $wpdb;
	$ocrb_backup =$wpdb->prefix."ocrb_backup";
	$wpdb->query( "DROP TABLE IF EXISTS $ocrb_backup" );
}
function activate_one_click_rollback_fun()
{
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	global $wpdb;
	$ocrb_backup =$wpdb->prefix."ocrb_backup";
	
	$sqlcreate = "CREATE TABLE $ocrb_backup
	(id int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(id),
		event_name varchar(255),
		details longtext,
		backup_date varchar(255),
		file_path longtext,
		status varchar(255)
	)";
	dbDelta( $sqlcreate );
	
	if (!is_dir(WP_CONTENT_DIR.'/wprecovery_backup/')) 
	{
    @mkdir(WP_CONTENT_DIR.'/wprecovery_backup/');
	}
	
	chl_backup();
}
add_action('admin_menu', 'one_click_rollback_menu');
/*Defines enqueue style/ script for dashboard*/
function one_click_rollback_scripts() {
	wp_enqueue_script( 'jquery' );
	
    wp_enqueue_style( 'one-click-rollback-bootstrap', plugin_dir_url(__FILE__) . 'bootstrap/one-click-rollback-bootstrap.css');
	
	wp_enqueue_script( 'one-click-rollback-bootstrap',  plugin_dir_url(__FILE__) . 'bootstrap/one-click-rollback-bootstrap.js' );
	wp_enqueue_style( 'ocrb', plugin_dir_url(__FILE__) . 'bootstrap/ocrb.css');
}
add_action( 'admin_init', 'one_click_rollback_scripts' );
/*Defines menu and sub-menu items in dashboard*/
function one_click_rollback_menu()
{
	add_menu_page("WPRecovery","WPRecovery","manage_options","ocrb_settings","ocrb_settings","dashicons-backup");
	add_submenu_page("ocrb_settings","Support","Support","manage_options","ocrb_support","ocrb_support","dashicons-backup");
}
function ocrb_settings()
{
	include 'settings.php';
}
function ocrb_support()
{
	include 'support.php';
}
if ( ! defined( 'OCRB_PLUGIN_SLUG' ) )
	define( 'OCRB_PLUGIN_SLUG', basename( dirname( __FILE__ ) ) );
if ( ! defined( 'OCRB_PLUGIN_PATH' ) )
	define( 'OCRB_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'OCRB_PLUGIN_URL' ) )
	define( 'OCRB_PLUGIN_URL', plugin_dir_url(  __FILE__  ) );
define( 'OCRB_PLUGIN_LANG_DIR', apply_filters( 'ocrb_filter_lang_dir', OCRB_PLUGIN_SLUG . '/languages/' ) );
if ( ! defined( 'OCRB_ADMIN_URL' ) )
	define( 'OCRB_ADMIN_URL', add_query_arg( 'page', OCRB_PLUGIN_SLUG, admin_url( 'admin.php' ) ) );
$key = array( ABSPATH, time() );
foreach ( array( 'AUTH_KEY', 'SECURE_AUTH_KEY', 'LOGGED_IN_KEY', 'NONCE_KEY', 'AUTH_SALT', 'SECURE_AUTH_SALT', 'LOGGED_IN_SALT', 'NONCE_SALT', 'SECRET_KEY' ) as $constant )
	if ( defined( $constant ) )
		$key[] = constant( $constant );
shuffle( $key );
define( 'OCRB_SECURE_KEY', md5( serialize( $key ) ) );
if ( ! defined( 'OCRB_REQUIRED_WP_VERSION' ) )
	define( 'OCRB_REQUIRED_WP_VERSION', '3.3.3' );
// Max memory limit isn't defined in old versions of WordPress
if ( ! defined( 'WP_MAX_MEMORY_LIMIT' ) )
	define( 'WP_MAX_MEMORY_LIMIT', '256M' );
if ( ! defined( 'OCRB_SCHEDULE_TIME' ) )
	define( 'OCRB_SCHEDULE_TIME', '11pm' );
if ( ! defined( 'OCRB_REQUIRED_PHP_VERSION' ) )
	define( 'OCRB_REQUIRED_PHP_VERSION', '5.2.4' );
if ( ! defined( 'MINUTE_IN_SECONDS' ) )
	define( 'MINUTE_IN_SECONDS', 60 );
if ( ! defined( 'HOUR_IN_SECONDS' ) )
	define( 'HOUR_IN_SECONDS',   60 * MINUTE_IN_SECONDS );
if ( ! defined( 'DAY_IN_SECONDS' ) )
	define( 'DAY_IN_SECONDS',    24 * HOUR_IN_SECONDS   );
if ( ! defined( 'WEEK_IN_SECONDS' ) )
	define( 'WEEK_IN_SECONDS',    7 * DAY_IN_SECONDS    );
if ( ! defined( 'YEAR_IN_SECONDS' ) )
	define( 'YEAR_IN_SECONDS',  365 * DAY_IN_SECONDS    );
// Load OCR_Backup
if ( ! class_exists( 'OCR_Backup' ) )
	require_once( OCRB_PLUGIN_PATH . 'classes/backup.php' );
// Load the schedules
require_once( OCRB_PLUGIN_PATH . 'classes/schedule.php' );
require_once( OCRB_PLUGIN_PATH . 'classes/class-wpbp-restore.php' );
//require_once( OCRB_PLUGIN_PATH . 'functions/interface.php' );
function chl_backup()
{
	//print_r($_GET);die;
	$values = serialize($_GET);
	global $wpdb;
	$action = $_GET['action'];
	$time = date("jS F g:i a");
	
	wp_schedule_single_event( time() + 5 , 'one_click_backup',array( "$action", "$values","$time") );
	//wp_schedule_single_event( time()+60,'one_click_backup', $args );
}

if(isset($_GET['action']) && $_GET['action']=='upgrade-plugin')
{
	chl_backup();
}
if(isset($_GET['action']) && $_GET['action']=='upgrade-theme')
{
	chl_backup();
}
@add_action( 'switch_theme','chl_backup');
add_action( 'deactivated_plugin','chl_backup');
add_action( 'activated_plugin','chl_backup');
add_action( '_core_updated_successfully','chl_backup');
function one_click_backup_function($a,$b,$c) {
	
	global $wpdb;
	$ocrb_backup =$wpdb->prefix."ocrb_backup";
	$qry = "select * from $ocrb_backup order by id desc limit 19,20";
	$results = $wpdb->get_results($qry);
	if(!empty($results))
	{
	  foreach($results as $file)
	  {
	  @unlink($file->file_path);
	  $qry = "delete from $ocrb_backup where id=".$file->id;
	  $wpdb->query($qry);	
	  }
	}
	$wpdb->insert($ocrb_backup,array('id' => '','event_name' =>$a,'details' => $b,'backup_date'=>$c,'file_path' =>$path,'status'=>'processing'),array( '%d','%s','%s','%s','%s','%s'));
$id = $wpdb->insert_id;
	$backup_class = new OCR_Backup;
	$path = $backup_class->backup();
	
	$qryupdate = "update $ocrb_backup set status='complete',file_path='".$path."' where id=".$id;
	$wpdb->query($qryupdate);
}
add_action( 'one_click_backup', 'one_click_backup_function',10,3);
add_action('wp_ajax_rollback', 'ocrb_rollback');
add_action('wp_ajax_nopriv_rollback', 'ocrb_rollback');
function ocrb_rollback()
{
	global $wpdb;
	include('rollback.php');
	die;
}
add_action('wp_ajax_delete_ocrb_backup', 'delete_ocrb_backup');
add_action('wp_ajax_nopriv_delete_ocrb_backup', 'delete_ocrb_backup');
function delete_ocrb_backup()
{
	global $wpdb;
	include('delete_backup.php');
	die;
}
?>