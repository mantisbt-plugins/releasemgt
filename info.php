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

define( 'PLUGINS_RELEASEMGT_VERSION', '0.0.3' );

function releasemgt_get_info() {
    $t_main_menu = config_get( 'main_menu_custom_options' );
    $t_access_level = ADMINISTRATOR;
    foreach( $t_main_menu as $t_menu ) {
        if ( ereg( 'plugins_page.php\?plugin=releasemgt&display=releasemgt', $t_menu[2] ) ) {
            $t_access_level = $t_menu[1];
        }
    }
    
    return array(
                 'name' => 'ReleaseMgt',
                 'version' => PLUGINS_RELEASEMGT_VERSION,
                 'url' => 'http://deboutv.free.fr/mantis/plugin.php?plugin=ReleaseMgt',
                 'check' => 'http://deboutv.free.fr/mantis/check.php?plugin=ReleaseMgt',
                 'bypass' => $t_access_level,
                 'upgrade' => 'http://deboutv.free.fr/mantis/check.php?plugin=ReleaseMgt&upgrade=1',
                 'check_unstable' => 'http://bugtracker.morinie.fr/plugins/check.php?plugin=ReleaseMgt',
                 'upgrade_unstable' => 'http://bugtracker.morinie.fr/plugins/check.php?upgrade=1&plugin=ReleaseMgt' );
}

?>
