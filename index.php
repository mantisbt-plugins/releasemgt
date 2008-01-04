<?php

/**
 * ReleaseMgt plugin
 *
 *
 * Created: 2008-01-05
 * Last update: 2008-01-07
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright 
 * @author Vincent DEBOUT <deboutv@free.fr>
 */

if ( !ereg( 'plugins_page.php', $_SERVER['PHP_SELF'] ) ) {
    header( 'Location: ../../plugins_page.php' );
    exit();
}

$t_display = gpc_get_string( 'display', 'configuration' );

$t_display_array = array( 'configuration', 'releasemgt', 'upload', 'download', 'delete' );

if ( in_array( $t_display, $t_display_array ) ) {
    require( $t_display . '_inc.php' );
} else {
    header( 'Location: plugins_page.php' );
}

?>
