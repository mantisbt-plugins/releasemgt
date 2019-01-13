<?php
/**
 * ReleaseMgt plugin
 *
 * Original author Vincent DEBOUT
 * modified for new Mantis plugin system by Jiri Hron
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright Copyright (c) 2008 Vincent Debout
 * @copyright Copyright (c) 2012 Jiri Hron
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 * @author Jiri Hron <jirka.hron@gmail.com>
 * @author F12 Ltd. <public@f12.com>
 */

require_once( 'core.php' );
require_once( 'bug_api.php' );
require_once( 'releasemgt_api.php' );
require_once( 'releasemgt_email_api.php' );

$t_file_count = gpc_get_int( 'file_count' );
$t_file = array();
$t_description = array();
for( $i=0; $i<$t_file_count; $i++ ) {
    $t_file[$i] = gpc_get_file( 'file_' . $i );
    $t_description[$i] = gpc_get_string( 'description_' . $i, '' );
}
$t_version = gpc_get_int( 'release', 0 );

$t_current_user_id = auth_get_current_user_id();
$t_project_id = helper_get_current_project();

// The same condition as for the upload controls displayed:
access_ensure_project_level( plugin_config_get( 'upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ), $t_project_id, $t_current_user_id );

for( $i=0; $i<$t_file_count; $i++ ) {
    $t_file_error[$i] = ( isset( $t_file[$i]['error'] ) ) ? $t_file[$i]['error'] : 0;
    $t_file_id[$i] = plugins_releasemgt_file_add( $t_file[$i]['tmp_name'], $t_file[$i]['name'], $t_file[$i]['type'], $t_project_id, $t_version, $t_description[$i], $t_file_error[$i] );
}

if ( plugin_config_get( 'notification_enable', PLUGINS_RELEASEMGT_NOTIFICATION_ENABLE_DEFAULT ) == ON ) {
    releasemgt_plugin_send_email( $t_project_id, $t_version, $t_file, $t_description, $t_file_id );
}

release_mgt_successful_redirect( 'releases' );
