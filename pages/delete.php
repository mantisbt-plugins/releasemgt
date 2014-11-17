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

$t_id = gpc_get_int( 'id' );

$t_current_user_id = auth_get_current_user_id();
$t_project_id = plugins_releasemgt_file_get_field($t_id, 'project_id');

// To ensure that the user will be able to download file only if he/she has at least the configured access level to the project:
access_ensure_project_level( plugin_config_get( 'upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ), $t_project_id, $t_current_user_id );

plugins_releasemgt_file_delete( $t_id );

release_mgt_successful_redirect(plugin_page( 'releases', true ));

?>