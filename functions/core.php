<?php





/**


 * Setup the plugin defaults on activation


 */


function ocrb_activate() {





	// loads the translation files


	load_plugin_textdomain( 'ocrb', false, OCRB_PLUGIN_LANG_DIR );





	// Run deactivate on activation in-case it was deactivated manually


	ocrb_deactivate();





}





/**


 * Cleanup on plugin deactivation


 *


 * Removes options and clears all cron schedules


 */


function ocrb_deactivate() {





	// Clean up the backups directory


	ocrb_cleanup();





	// Remove the plugin data cache


	delete_transient( 'ocrb_plugin_data' );





	$schedules = OCRB_Schedules::get_instance();





	// Clear schedule crons


	foreach ( $schedules->get_schedules() as $schedule )


		$schedule->unschedule();





}





/**


 * Handles anything that needs to be


 * done when the plugin is updated


 */


function ocrb_update() {





	// Every update


	if ( get_option( 'ocrb_plugin_version' ) && version_compare( OCRB_VERSION, get_option( 'ocrb_plugin_version' ), '>' ) ) {





		ocrb_deactivate();





		// re-calcuate the backups directory and move to it.


		if ( ! defined( 'OCRB_PATH' ) ) {





			$old_path = ocrb_path();





			delete_option( 'ocrb_path' );


			delete_option( 'ocrb_default_path' );





			ocrb_path_move( $old_path, ocrb_path() );





		}





		// Force .htaccess to be re-written


		if ( file_exists( ocrb_path() . '/.htaccess' ) )


			unlink( ocrb_path() . '/.htaccess' );





		// Force index.html to be re-written


		if ( file_exists( ocrb_path() . '/index.html' ) )


			unlink( ocrb_path() . '/index.html' );





	}





	// Update the stored version


	if ( get_option( 'ocrb_plugin_version' ) !== OCRB_VERSION )


		update_option( 'ocrb_plugin_version', OCRB_VERSION );





}





/**


 * Setup the default backup schedules


 */


function ocrb_setup_default_schedules() {





	$schedules = OCRB_Schedules::get_instance();





	if ( $schedules->get_schedules() )


		return;





	/**


	 * Schedule a database backup daily and store backups


	 * for the last 2 weeks


	 */


	$database_daily = new OCRB_Scheduled_Backup( 'default-1' );


	$database_daily->set_type( 'database' );


	$database_daily->set_reoccurrence( 'ocrb_daily' );


	$database_daily->set_max_backups( 14 );


	$database_daily->save();





	/**


	 * Schedule a complete backup to run weekly and store backups for


	 * the last 3 months


	 */


	$complete_weekly = new OCRB_Scheduled_Backup( 'default-2' );


	$complete_weekly->set_type( 'complete' );


	$complete_weekly->set_reoccurrence( 'ocrb_weekly' );


	$complete_weekly->set_max_backups( 12 );


	$complete_weekly->save();





	function ocrb_default_schedules_setup_warning() {


		echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup has setup your default schedules.', 'ocrb' ) . '</strong> ' . __( 'By default WP Backup performs a daily backup of your database and a weekly backup of your database &amp; files. You can modify these schedules.', 'ocrb' ) . '</p></div>';


	}


	add_action( 'admin_notices', 'ocrb_default_schedules_setup_warning' );





}


add_action( 'admin_init', 'ocrb_setup_default_schedules' );





/**


 * Return an array of cron schedules


 *


 * @param $schedules


 * @return array $reccurrences


 */


function ocrb_cron_schedules( $schedules ) {





	$schedules['ocrb_hourly']      = array( 'interval' => HOUR_IN_SECONDS, 'display'      => __( 'Once Hourly', 'ocrb' ) );


	$schedules['ocrb_twicedaily'] 	= array( 'interval' => 12 * HOUR_IN_SECONDS, 'display' => __( 'Twice Daily', 'ocrb' ) );


	$schedules['ocrb_daily']      	= array( 'interval' => DAY_IN_SECONDS, 'display'       => __( 'Once Daily', 'ocrb' ) );


	$schedules['ocrb_weekly'] 		= array( 'interval' => WEEK_IN_SECONDS, 'display'      => __( 'Once Weekly', 'ocrb' ) );


	$schedules['ocrb_fortnightly']	= array( 'interval' => 2 * WEEK_IN_SECONDS , 'display' => __( 'Once Fortnightly', 'ocrb' ) );


	$schedules['ocrb_monthly']		= array( 'interval' => 30 * DAY_IN_SECONDS, 'display'  => __( 'Once Monthly', 'ocrb' ) );





	return $schedules;


}


add_filter( 'cron_schedules', 'ocrb_cron_schedules' );





/**


 * Recursively delete a directory including


 * all the files and sub-directories.


 *


 * @param string $dir


 * @return bool


 * @throws Exception


 */


function ocrb_rmdirtree( $dir ) {





	if ( strpos( OCR_Backup::get_home_path(), $dir ) !== false )


		throw new Exception( 'You can only delete directories inside your WordPress installation' );





	if ( is_file( $dir ) )


		@unlink( $dir );





    if ( ! is_dir( $dir ) )


    	return false;





    $files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $dir ), RecursiveIteratorIterator::CHILD_FIRST, RecursiveIteratorIterator::CATCH_GET_CHILD );





	foreach ( $files as $file ) {





		if ( $file->isDir() )


			@rmdir( $file->getPathname() );





		else


			@unlink( $file->getPathname() );





	}





	@rmdir( $dir );





}





/**


 * Get the path to the backups directory


 *


 * Will try to create it if it doesn't exist


 * and will fallback to default if a custom dir


 * isn't writable.


 */


function ocrb_path() {





	global $is_apache;





	$path = untrailingslashit( get_option( 'ocrb_path' ) );





	// Allow the backups path to be defined


	if ( defined( 'OCRB_PATH' ) && OCRB_PATH )


		$path = untrailingslashit( OCRB_PATH );





	// If the dir doesn't exist or isn't writable then use the default path instead instead


	if ( ( ! $path || ( is_dir( $path ) && ! is_writable( $path ) ) || ( ! is_dir( $path ) && ! is_writable( dirname( $path ) ) ) ) && $path !== ocrb_path_default() )


		$path = ocrb_path_default();





	// Create the backups directory if it doesn't exist


	if ( ! is_dir( $path ) && is_writable( dirname( $path ) ) )


		mkdir( $path, 0755 );





	// If the path has changed then cache it


	if ( get_option( 'ocrb_path' ) !== $path )


		update_option( 'ocrb_path', $path );





	// Protect against directory browsing by including a index.html file


	$index = $path . '/index.html';





	if ( ! file_exists( $index ) && is_writable( $path ) )


		file_put_contents( $index, '' );





	$htaccess = $path . '/.htaccess';





	// Protect the directory with a .htaccess file on Apache servers


	if ( $is_apache && function_exists( 'insert_with_markers' ) && ! file_exists( $htaccess ) && is_writable( $path ) ) {





		$contents[]	= '# ' . sprintf( __( 'This %s file ensures that other people cannot download your backup files.', 'ocrb' ), '.htaccess' );


		$contents[] = '';


		$contents[] = '<IfModule mod_rewrite.c>';


		$contents[] = 'RewriteEngine On';


		$contents[] = 'RewriteCond %{QUERY_STRING} !key=' . OCRB_SECURE_KEY;


		$contents[] = 'RewriteRule (.*) - [F]';


		$contents[] = '</IfModule>';


		$contents[] = '';





		insert_with_markers( $htaccess, 'WP Backup', $contents );





	}





    return OCR_Backup::conform_dir( $path );





}





/**


 * Return the default backup path


 *


 * @return string path


 */


function ocrb_path_default() {





	$path = untrailingslashit( get_option( 'ocrb_default_path' ) );





	if ( empty( $path ) ) {





		$path = OCR_Backup::conform_dir( trailingslashit( WP_CONTENT_DIR ) . 'backupwordpress-' . substr( OCRB_SECURE_KEY, 0, 10 ) . '-backups' );





		update_option( 'ocrb_default_path', $path );





	}





	$upload_dir = wp_upload_dir();





	// If the backups dir can't be created in WP_CONTENT_DIR then fallback to uploads


	if ( ( ( ! is_dir( $path ) && ! is_writable( dirname( $path ) ) ) || ( is_dir( $path ) && ! is_writable( $path ) ) ) && strpos( $path, $upload_dir['basedir'] ) === false ) {





		ocrb_path_move( $path, $path = OCR_Backup::conform_dir( trailingslashit( $upload_dir['basedir'] ) . 'backupwordpress-' . substr( OCRB_SECURE_KEY, 0, 10 ) . '-backups' ) );





		update_option( 'ocrb_default_path', $path );





	}





	return $path;





}





/**


 * Move the backup directory and all existing backup files to a new


 * location


 *


 * @param string $from path to move the backups dir from


 * @param string $to path to move the backups dir to


 * @return void


 */


function ocrb_path_move( $from, $to ) {





	if ( ! trim( untrailingslashit( trim( $from ) ) ) || ! trim( untrailingslashit( trim( $to ) ) ) )


		return;





	// Create the new directory if it doesn't exist


	if ( is_writable( dirname( $to ) ) && ! is_dir( $to ) )


	    mkdir( $to, 0755 );





	// Bail if we couldn't


	if ( ! is_dir( $to ) || ! is_writable( $to ) )


	    return false;





	update_option( 'ocrb_path', $to );





	// Bail if the old directory doesn't exist


	if ( ! is_dir( $from ) )


		return false;





	// Cleanup before we start moving things


	ocrb_cleanup();





	// Move any existing backups


	if ( $handle = opendir( $from ) ) {





	    while ( false !== ( $file = readdir( $handle ) ) )


	    	if ( pathinfo( $file, PATHINFO_EXTENSION ) === 'zip' )


	    		if ( ! @rename( trailingslashit( $from ) . $file, trailingslashit( $to ) . $file ) )


	    			copy( trailingslashit( $from ) . $file, trailingslashit( $to ) . $file );





	    closedir( $handle );





	}





	// Only delete the old directory if it's inside WP_CONTENT_DIR


	if ( strpos( $from, WP_CONTENT_DIR ) !== false )


		ocrb_rmdirtree( $from );





}





/**


 * Check if a backup is possible with regards to file


 * permissions etc.


 *


 * @return bool


 */


function ocrb_possible() {





	if ( ! is_writable( ocrb_path() ) || ! is_dir( ocrb_path() ) )


		return false;





	return true;


}





/**


 * Remove any non backup.zip files from the backups dir.


 *


 * @return void


 */


function ocrb_cleanup() {





	if ( defined( 'OCRB_PATH' ) && OCRB_PATH )


		return;





	$ocrb_path = ocrb_path();





	if ( ! is_dir( $ocrb_path ) )


		return;





	if ( $handle = opendir( $ocrb_path ) ) {





    	while ( false !== ( $file = readdir( $handle ) ) )


    		if ( ! in_array( $file, array( '.', '..', 'index.html' ) ) && pathinfo( $file, PATHINFO_EXTENSION ) !== 'zip' )


				ocrb_rmdirtree( trailingslashit( $ocrb_path ) . $file );





    	closedir( $handle );





    }





}





/**


 * Handles changes in the defined Constants


 * that users can define to control advanced


 * settings


 */


function ocrb_constant_changes() {





	// If a custom backup path has been set or changed


	if ( defined( 'OCRB_PATH' ) && OCRB_PATH && OCR_Backup::conform_dir( OCRB_PATH ) !== ( $from = OCR_Backup::conform_dir( get_option( 'ocrb_path' ) ) ) )


	  ocrb_path_move( $from, OCRB_PATH );





	// If a custom backup path has been removed


	if ( ( ( defined( 'OCRB_PATH' ) && ! OCRB_PATH ) || ! defined( 'OCRB_PATH' ) && ocrb_path_default() !== ( $from = OCR_Backup::conform_dir( get_option( 'ocrb_path' ) ) ) ) )


	  ocrb_path_move( $from, ocrb_path_default() );





	// If the custom path has changed and the new directory isn't writable


	if ( defined( 'OCRB_PATH' ) && OCRB_PATH && ! is_writable( OCRB_PATH ) && get_option( 'ocrb_path' ) === OCRB_PATH && is_dir( OCRB_PATH ) )


		ocrb_path_move( OCRB_PATH, ocrb_path_default() );





}





/**


 * Get the max email attachment filesize


 *


 * Can be overridden by defining OCRB_ATTACHMENT_MAX_FILESIZE


 *


 * return int the filesize


 */


function ocrb_get_max_attachment_size() {





	$max_size = '10mb';





	if ( defined( 'OCRB_ATTACHMENT_MAX_FILESIZE' ) && wp_convert_hr_to_bytes( OCRB_ATTACHMENT_MAX_FILESIZE ) )


		$max_size = OCRB_ATTACHMENT_MAX_FILESIZE;





	return wp_convert_hr_to_bytes( $max_size );





}


