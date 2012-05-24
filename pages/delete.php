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
require_once( 'releasemgt_api.php' );

$t_id = gpc_get_int( 'id' );

$t_current_user_id = auth_get_current_user_id();

if ( user_get_access_level( $t_current_user_id) < plugin_config_get( 'upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ) ) {
    access_denied();
}

plugins_releasemgt_file_delete( $t_id );

release_mgt_successful_redirect(plugin_page( 'releases', true ));

?>