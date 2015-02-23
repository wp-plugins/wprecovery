<?php



	/**

	 * Displays a row in the manage backups table

	 *

	 * @param string $file

	 * @param OCRB_Scheduled_Backup $schedule

	 */

function ocrb_get_backup_row( $file, OCRB_Scheduled_Backup $schedule ) {



	$encoded_file = urlencode( base64_encode( $file ) );

	$offset = get_option( 'gmt_offset' ) * 3600; ?>



	<tr class="ocrb_manage_backups_row<?php if ( file_exists( ocrb_path() . '/.backup_complete' ) ) : ?> completed<?php unlink( ocrb_path() . '/.backup_complete' ); endif; ?>">



		<th scope="row">

			<?php echo esc_html( date_i18n( get_option( 'date_format' ) . ' - ' . get_option( 'time_format' ), @filemtime( $file ) + $offset ) ); ?>

		</th>



		<td class="code">

			<?php echo esc_html( size_format( @filesize( $file ) ) ); ?>

		</td>



		<td><?php echo esc_html( ocrb_human_get_type( $file, $schedule ) ); ?></td>



		<td>



			<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=' . OCRB_PLUGIN_SLUG . '&amp;ocrb_download_backup=' . $encoded_file . '&amp;ocrb_schedule_id=' . $schedule->get_id() ), 'ocrb-download_backup' ); ?>"><?php _e( 'Download', 'ocrb' ); ?></a> |

			<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=' . OCRB_PLUGIN_SLUG . '&amp;ocrb_delete_backup=' . $encoded_file . '&amp;ocrb_schedule_id=' . $schedule->get_id() ), 'ocrb-delete_backup' ); ?>" class="delete-action"><?php _e( 'Delete', 'ocrb' ); ?></a> |

			<a href="<?php echo wp_nonce_url( admin_url( 'admin.php?page=' . OCRB_PLUGIN_SLUG . '&amp;ocrb_restore_backup=' . $encoded_file . '&amp;ocrb_schedule_id=' . $schedule->get_id() ), 'ocrb-restore_backup' ); ?>" style="color: green" class="restore-action"><?php _e( 'Restore', 'ocrb' ); ?></a>

			



		</td>



	</tr>



<?php }



/**

 * Displays admin notices for various error / warning

 * conditions

 *

 * @return void

 */

function ocrb_admin_notices() {



	// If the backups directory doesn't exist and can't be automatically created

	if ( ! is_dir( ocrb_path() ) ) :



		function ocrb_path_exists_warning() {

			$php_user = exec( 'whoami' );

			$php_group = reset( explode( ' ', exec( 'groups' ) ) );

			echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup is almost ready.', 'ocrb' ) . '</strong> ' . sprintf( __( 'The backups directory can\'t be created because your %1$s directory isn\'t writable, run %2$s or %3$s or create the folder yourself.', 'ocrb' ), '<code>wp-content</code>', '<code>chown ' . esc_html( $php_user ) . ':' . esc_html( $php_group ) . ' ' . esc_html( dirname( ocrb_path() ) ) . '</code>', '<code>chmod 777 ' . esc_html( dirname( ocrb_path() ) ) . '</code>' ) . '</p></div>';

		}

		add_action( 'admin_notices', 'ocrb_path_exists_warning' );



	endif;



	// If the backups directory exists but isn't writable

	if ( is_dir( ocrb_path() ) && ! is_writable( ocrb_path() ) ) :



		function ocrb_writable_path_warning() {

			$php_user = exec( 'whoami' );

			$php_group = reset( explode( ' ', exec( 'groups' ) ) );

			echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup is almost ready.', 'ocrb' ) . '</strong> ' . sprintf( __( 'Your backups directory isn\'t writable, run %1$s or %2$s or set the permissions yourself.', 'ocrb' ), '<code>chown -R ' . esc_html( $php_user ) . ':' . esc_html( $php_group ) . ' ' . esc_html( ocrb_path() ) . '</code>', '<code>chmod -R 777 ' . esc_html( ocrb_path() ) . '</code>' ) . '</p></div>';

		}

		add_action( 'admin_notices', 'ocrb_writable_path_warning' );



	endif;



	// If safe mode is active

	if ( OCR_Backup::is_safe_mode_active() ) :



		function ocrb_safe_mode_warning() {

			echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup has detected a problem.', 'ocrb' ) . '</strong> ' . sprintf( __( '%1$s is running in %2$s, please contact your host and ask them to disable it. WP Backup may not work correctly whilst %3$s is on.', 'ocrb' ), '<code>PHP</code>', sprintf( '<a href="%1$s">%2$s</a>', __( 'http://php.net/manual/en/features.safe-mode.php', 'ocrb' ), __( 'Safe Mode', 'ocrb' ) ), '<code>' . __( 'Safe Mode', 'ocrb' ) . '</code>' ) . '</p></div>';

		}

		add_action( 'admin_notices', 'ocrb_safe_mode_warning' );



	endif;



	// If a custom backups directory is defined and it doesn't exist and can't be created

	if ( defined( 'OCRB_PATH' ) && OCRB_PATH && ! is_dir( OCRB_PATH ) ) :



		function ocrb_custom_path_exists_warning() {

			echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup has detected a problem.', 'ocrb' ) . '</strong> ' . sprintf( __( 'Your custom backups directory %1$s doesn\'t exist and can\'t be created, your backups will be saved to %2$s instead.', 'ocrb' ), '<code>' . esc_html( OCRB_PATH ) . '</code>', '<code>' . esc_html( ocrb_path() ) . '</code>' ) . '</p></div>';

		}

		add_action( 'admin_notices', 'ocrb_custom_path_exists_warning' );



	endif;



	// If a custom backups directory is defined and exists but isn't writable

	if ( defined( 'OCRB_PATH' ) && OCRB_PATH && is_dir( OCRB_PATH ) && ! is_writable( OCRB_PATH ) ) :



		function ocrb_custom_path_writable_notice() {

			echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup has detected a problem.', 'ocrb' ) . '</strong> ' . sprintf( __( 'Your custom backups directory %1$s isn\'t writable, new backups will be saved to %2$s instead.', 'ocrb' ), '<code>' . esc_html( OCRB_PATH ) . '</code>', '<code>' . esc_html( ocrb_path() ) . '</code>' ) . '</p></div>';

		}

		add_action( 'admin_notices', 'ocrb_custom_path_writable_notice' );



	endif;



	// If there are any errors reported in the backup

	if ( ocrb_backup_errors_message() ) :



		function ocrb_backup_errors_notice() {

			echo '<div id="ocrb-warning" class="updated fade"><p><strong>' . __( 'WP Backup detected issues with your last backup.', 'ocrb' ) . '</strong><a href="' . add_query_arg( 'action', 'ocrb_dismiss_error' ) . '" style="float: right;" class="button">Dismiss</a></p>' . ocrb_backup_errors_message() . '</div>';

		}

		add_action( 'admin_notices', 'ocrb_backup_errors_notice' );



	endif;



}

add_action( 'admin_head', 'ocrb_admin_notices' );



	/**

	 * Hook in an change the plugin description when WP Backup is activated

	 *

	 * @param array $plugins

	 * @return array $plugins

	 */

function ocrb_plugin_row( $plugins ) {



	if ( isset( $plugins[OCRB_PLUGIN_SLUG . '/plugin.php'] ) )

		$plugins[OCRB_PLUGIN_SLUG . '/plugin.php']['Description'] = str_replace( 'Once activated you\'ll find me under <strong>Tools &rarr; Backups</strong>', 'Find me under <strong><a href="' . admin_url( 'admin.php?page=' . OCRB_PLUGIN_SLUG ) . '">Tools &rarr; Backups</a></strong>', $plugins[OCRB_PLUGIN_SLUG . '/plugin.php']['Description'] );



	return $plugins;



}

add_filter( 'all_plugins', 'ocrb_plugin_row', 10 );



/**

 * Parse the json string of errors and

 * output as a human readable message

 *

 * @access public

 * @return null

 */

function ocrb_backup_errors_message() {



	$message = '';



	foreach ( (array) json_decode( ocrb_backup_errors() ) as $key => $errors )

		foreach ( $errors as $error )

			$message .= '<p><strong>' . esc_html( $key ) . '</strong>: <code>' . implode( ':', array_map( 'esc_html', (array) $error ) ) . '</code></p>';



	return $message;



}



/**

 * Display a html list of files

 *

 * @param OCRB_Scheduled_Backup $schedule

 * @param mixed $excludes (default: null)

 * @param string $file_method (default: 'get_included_files')

 * @return void

 */

function ocrb_file_list( OCRB_Scheduled_Backup $schedule, $excludes = null, $file_method = 'get_included_files' ) {



	if ( ! is_null( $excludes ) )

		$schedule->set_excludes( $excludes );



	$exclude_string = $schedule->exclude_string( 'regex' ); ?>



	<ul class="ocrb_file_list code">



		<?php foreach( $schedule->get_files() as $file ) :



			if ( ! is_null( $excludes ) && strpos( $file, str_ireplace( $schedule->get_root(), '', $schedule->get_path() ) ) !== false )

				continue;



			// Skip dot files, they should only exist on versions of PHP between 5.2.11 -> 5.3

			if ( method_exists( $file, 'isDot' ) && $file->isDot() )

				continue;



			// Show only unreadable files

			if ( $file_method === 'get_unreadable_files' && @realpath( $file->getPathname() ) && $file->isReadable() )

			   	continue;



			// Skip unreadable files

			elseif ( $file_method !== 'get_unreadable_files' && ( ! @realpath( $file->getPathname() ) || ! $file->isReadable() ) )

				continue;



			// Show only included files

			if ( $file_method === 'get_included_files' )

				if ( $exclude_string && preg_match( '(' . $exclude_string . ')', str_ireplace( trailingslashit( $schedule->get_root() ), '', OCR_Backup::conform_dir( $file->getPathname() ) ) ) )

					continue;



			// Show only excluded files

			if ( $file_method === 'get_excluded_files' )

			    if ( ! $exclude_string || ! preg_match( '(' .  $exclude_string . ')', str_ireplace( trailingslashit( $schedule->get_root() ), '', OCR_Backup::conform_dir( $file->getPathname() ) ) ) )

			    	continue;



			if ( @realpath( $file->getPathname() ) && ! $file->isReadable() && $file->isDir() ) { ?>



		<li title="<?php echo esc_attr( OCR_Backup::conform_dir( trailingslashit( $file->getPathName() ) ) ); ?>"><?php echo esc_html( ltrim( trailingslashit( str_ireplace( OCR_Backup::conform_dir( trailingslashit( $schedule->get_root() ) ), '', OCR_Backup::conform_dir( $file->getPathName() ) ) ), '/' ) ); ?></li>



			<?php } else { ?>



		<li title="<?php echo esc_attr( OCR_Backup::conform_dir( $file->getPathName() ) ); ?>"><?php echo esc_html( ltrim( str_ireplace( OCR_Backup::conform_dir( trailingslashit( $schedule->get_root() ) ), '', OCR_Backup::conform_dir( $file->getPathName() ) ), '/' ) ); ?></li>



			<?php }



		endforeach; ?>



	</ul>



<?php }





/**

 * Get the human readable backup type in.

 *

 * @access public

 * @param string $type

 * @param OCRB_Scheduled_Backup $schedule (default: null)

 * @return string

 */

function ocrb_human_get_type( $type, OCRB_Scheduled_Backup $schedule = null ) {



	if ( strpos( $type, 'complete' ) !== false )

		return __( 'Database and Files', 'ocrb' );



	if ( strpos( $type, 'file' ) !== false )

		return __( 'Files', 'ocrb' );



	if ( strpos( $type, 'database' ) !== false )

		return __( 'Database', 'ocrb' );



	if ( ! is_null( $schedule ) )

		return ocrb_human_get_type( $schedule->get_type() );



	return __( 'Legacy', 'ocrb' );



}



/**

 * Display the row of actions for a schedule

 *

 * @access public

 * @param OCRB_Scheduled_Backup $schedule

 * @return void

 */

function ocrb_schedule_actions( OCRB_Scheduled_Backup $schedule ) {



	// Start output buffering

	ob_start(); ?>



	<span class="ocrb-status"><?php echo $schedule->get_status() ? $schedule->get_status() : __( 'Starting Backup', 'ocrb' ); ?> <a href="<?php echo add_query_arg( array( 'action' => 'ocrb_cancel', 'ocrb_schedule_id' => $schedule->get_id() ), OCRB_ADMIN_URL ); ?>"><?php _e( 'cancel', 'ocrb' ); ?></a></span>



	<div class="ocrb-schedule-actions row-actions">



		<a class="colorbox" href="<?php echo add_query_arg( array( 'action' => 'ocrb_edit_schedule_load', 'ocrb_schedule_id' => $schedule->get_id() ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Settings', 'ocrb' ); ?></a> |



	<?php if ( $schedule->get_type() !== 'database' ) { ?>

		<a class="colorbox" href="<?php echo add_query_arg( array( 'action' => 'ocrb_edit_schedule_excludes_load', 'ocrb_schedule_id' => $schedule->get_id() ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Excludes', 'ocrb' ); ?></a>  |

	<?php } ?>



		<?php // capture output

		$output = ob_get_clean();

		echo apply_filters( 'ocrb_schedule_actions_menu', $output, $schedule ); ?>



		<a class="ocrb-run" href="<?php echo add_query_arg( array( 'action' => 'ocrb_run_schedule', 'ocrb_schedule_id' => $schedule->get_id() ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Run now', 'ocrb' ); ?></a>  |



		<a class="delete-action" href="<?php echo wp_nonce_url( add_query_arg( array( 'action' => 'ocrb_delete_schedule', 'ocrb_schedule_id' => $schedule->get_id() ), OCRB_ADMIN_URL ), 'ocrb-delete_schedule' ); ?>"><?php _e( 'Delete', 'ocrb' ); ?></a>



	</div>



<?php }





/**

 * Load the backup errors file

 *

 * @return string

 */

function ocrb_backup_errors() {



	if ( ! file_exists( ocrb_path() . '/.backup_errors' ) )

		return '';



	return file_get_contents( ocrb_path() . '/.backup_errors' );



}



/**

 * Load the backup warnings file

 *

 * @return string

 */

function ocrb_backup_warnings() {



	if ( ! file_exists( ocrb_path() . '/.backup_warnings' ) )

		return '';



	return file_get_contents( ocrb_path() . '/.backup_warnings' );



}



/**

 * Display the restore message

 */

function ocrb_restore_message() {



	if ( ! isset( $_GET['restore'] ) || ! isset( $_GET['page'] ) )

		return;



	if ( $_GET['restore'] == 'success' )

		printf( "<div id='message' class='updated'><p>Backup is successfully restored.</p></div>" );



}



add_action( 'admin_head', 'ocrb_restore_message' );