<?php

/**
 * ReleaseMgt plugin
 *
 *
 * Created: 2008-01-05
 * Last update: 2008-11-08
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright 
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 */

if ( !defined( 'PLUGINS_PM_OK' ) ) {
    header( 'Location: ../../plugins_page.php' );
    exit();
}

$t_id = gpc_get_int( 'id' );

$t_current_user_id = auth_get_current_user_id();

if ( user_get_access_level( $t_current_user_id) < config_get( 'plugins_releasemgt_upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ) ) {
    access_denied();
}

plugins_releasemgt_file_delete( $t_id );

$t_redirect_url = 'plugins_page.php?plugin=releasemgt&display=releasemgt';

html_page_top1();
html_meta_redirect( $t_redirect_url );
html_page_top2();

?>
<br />
<div align="center">
<?php
	echo lang_get( 'operation_successful' ) . '<br />';
	print_bracket_link( $t_redirect_url, lang_get( 'proceed' ) );
?>
</div>
<?php

html_page_bottom1( __FILE__ );

?>