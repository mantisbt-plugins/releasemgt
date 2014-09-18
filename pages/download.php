<?php
/**
 * ReleaseMgt plugin
 *
 * Original author Vincent DEBOUT
 * modified for new Mantis plugin system by Jiri Hron
 *
 * Created: 2008-01-05
 * Last update: 2013-05-03
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 * @author Jiri Hron <jirka.hron@gmail.com>
 * @author F12 Ltd. <public@f12.com>
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

if ($require_login)
{
// To ensure that only logged user will be able to download file:
	$t_current_user_id = auth_get_current_user_id();
// To ensure that the user will be able to download file only if he/she has at least REPORTER rights to the project:
	access_ensure_project_level( REPORTER, $v_project_id, $t_current_user_id );
}

# throw away output buffer contents (and disable it) to protect download
while ( @ob_end_clean() );

if ( ini_get( 'zlib.output_compression' ) && function_exists( 'ini_set' ) ) {
        ini_set( 'zlib.output_compression', false );
}

http_security_headers();

# Make sure that IE can download the attachments under https.
header( 'Pragma: public' );

# To fix an IE bug which causes problems when downloading
# attached files via HTTPS, we disable the "Pragma: no-cache"
# command when IE is used over HTTPS.
global $g_allow_file_cache;
if ( ( isset( $_SERVER["HTTPS"] ) && ( "on" == utf8_strtolower( $_SERVER["HTTPS"] ) ) ) && is_browser_internet_explorer() ) {
        # Suppress "Pragma: no-cache" header.
} else {
        if ( !isset( $g_allow_file_cache ) ) {
            header( 'Pragma: no-cache' );
        }
}
header( 'Expires: ' . gmdate( 'D, d M Y H:i:s \G\M\T', time() ) );

header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s \G\M\T', $v_date_added ) );

$t_filename = file_get_display_name( $v_filename );
# For Internet Explorer 8 as per http://blogs.msdn.com/ie/archive/2008/07/02/ie8-security-part-v-comprehensive-protection.aspx
# Don't let IE second guess our content-type!
header( 'X-Content-Type-Options: nosniff' );

//Would be better to use integrated function but it brakes filename of file
//http_content_disposition_header( $t_filename);
$t_disposition = 'attachment;';
if ( is_browser_internet_explorer() || is_browser_chrome() ) {
        // Internet Explorer does not support RFC2231 however it does
        // incorrectly decode URL encoded filenames and we can use this to
        // get UTF8 filenames to work with the file download dialog. Chrome
        // behaves in the same was as Internet Explorer in this respect.
        // See http://greenbytes.de/tech/tc2231/#attwithfnrawpctenclong
        header( 'Content-Disposition:' . $t_disposition . ' filename="' . $t_filename . '"' );
} else {
        // For most other browsers, we can use this technique:
        // http://greenbytes.de/tech/tc2231/#attfnboth2
        header( 'Content-Disposition:' . $t_disposition . ' filename*=UTF-8\'\'' . rawurlencode ( $t_filename ) . '; filename="' . $t_filename . '"' );
}

header( 'Content-Length: ' . $v_filesize );

header( 'Content-Type: ' . $v_file_type );

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