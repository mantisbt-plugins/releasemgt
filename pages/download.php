<?php
/**
 * ReleaseMgt plugin
 *
 * Original author Vincent DEBOUT
 * modified for new Mantis plugin system by Jiri Hron
 *
 * Created: 2008-01-05
 * Last update: 2012-05-23
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 * @author Jiri Hron <jirka.hron@gmail.com>
 */

require_once( 'core.php' );
require_once( 'bug_api.php' );

html_page_top( plugin_lang_get( 'display_page_title' ) );

$t_id = gpc_get_int( 'id' );
$c_file_id = db_prepare_int( $t_id );

$t_file_table = plugin_table('file');

$query = "SELECT *
		FROM $t_file_table
		WHERE id='$c_file_id'";
$result = db_query( $query );
$row = db_fetch_array( $result );

if (!$row){
    trigger_error( ERROR_FILE_NOT_FOUND, ERROR );
}

extract( $row, EXTR_PREFIX_ALL, 'v' );

$require_login = (bool) plugin_config_get('download_requires_login', null, false, NO_USER, $v_project_id);

//To ensure that only logged user will be able to download file
if ($require_login){
    auth_get_current_user_id();
}

@ob_end_clean();
header( 'Pragma: public' );

header( 'Content-Type: ' . $v_file_type );
header( 'Content-Length: ' . $v_filesize );
$t_filename = file_get_display_name( $v_filename );
$t_disposition = ' attachment;';

header( 'Content-Disposition:' . $t_disposition . ' filename="' . $t_filename . '"' );
header( 'Content-Description: Download Data' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $v_date_added ) );

global $g_allow_file_cache;
if ( ( isset( $_SERVER["HTTPS"] ) && ( "on" == strtolower( $_SERVER["HTTPS"] ) ) ) && preg_match( "/MSIE/", $_SERVER["HTTP_USER_AGENT"] ) ) {
# Suppress "Pragma: no-cache" header.
} else {
    if ( ! isset( $g_allow_file_cache ) ) {
        header( 'Pragma: no-cache' );
    }
}
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

switch ( plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) ) {
  case DISK:
    if ( file_exists( $v_diskfile ) ) {
        readfile( $v_diskfile );
    }
    break;
  case FTP:
    if ( file_exists( $v_diskfile ) ) {
        readfile( $v_diskfile );
    } else {
        $ftp = plugins_releasemgt_file_ftp_connect();
        file_ftp_get ( $ftp, $v_diskfile, $v_diskfile );
        file_ftp_disconnect( $ftp );
        readfile( $v_diskfile );
    }
    break;
  default:
    echo $v_content;
}
?>
<?php
html_page_bottom();
?>