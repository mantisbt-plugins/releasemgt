<?php

/**
 * This file is used to install the plugin manually.
 *
 * Created: 2008-01-05
 * Last update: 2008-11-08
 *
 * @link http://deboutv.free.fr/mantis/
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 * @package ReleaseMgt
 * @version 0.0.1
 */

if ( !ereg( 'plugins_install_page.php', $_SERVER['PHP_SELF'] ) && !ereg( 'plugins_uninstall_page.php', $_SERVER['PHP_SELF'] ) ) {
    header( 'Location: ../../plugins_page.php' );
    exit();
}

$t_plugins_plugin_manager_step_count = array();
$t_plugins_plugin_manager_step_count['install'] = 2;
$t_plugins_plugin_manager_step_count['upgrade'] = 0;
$t_plugins_plugin_manager_step_count['uninstall'] = 2;
$t_plugins_plugin_manager_step_count['mantis_upgrade'] = 0;
$t_plugins_plugin_manager_step_count['mantis_repair'] = 0;

function plugins_releasemgt_install_description_plugin( $p_step ) {
    if ( $p_step == 2 ) {
        return 'Add menu link';
    } else {
        return 'Create table';
    }
}

function plugins_releasemgt_upgrade_description_plugin( $p_step ) {
    return '';
}

function plugins_releasemgt_mantis_upgrade_description_plugin( $p_step ) {
    return '';
}

function plugins_releasemgt_mantis_repair_description_plugin( $p_step ) {
    return '';
}

function plugins_releasemgt_uninstall_description_plugin( $p_step ) {
    if ( $p_step == 2 ) {
        return 'Remove menu link';
    } else {
        return 'Delete table';
    }
}

function plugins_releasemgt_install_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    if ( $p_step == 1 ) {
        if ( db_is_connected() ) {
            if ( !db_table_exists( config_get( 'db_table_prefix' ) . '_plugins_releasemgt_file' . config_get( 'db_table_suffix' ) ) ) {
                $t_query = 'CREATE TABLE ' . config_get( 'db_table_prefix' ) . '_plugins_releasemgt_file' . config_get( 'db_table_suffix' ) . ' ( id int(10) unsigned not null auto_increment, project_id int(10) unsigned NOT NULL, version_id int(10) unsigned NOT NULL, title varchar(250) NOT NULL default \'\', description varchar(250) NOT NULL default \'\', diskfile varchar(250) NOT NULL default \'\', filename varchar(250) NOT NULL default \'\', folder varchar(250) NOT NULL default \'\', filesize int(11) NOT NULL default \'0\', file_type varchar(250) NOT NULL default \'\', date_added datetime NOT NULL default \'1970-01-01 00:00:01\', content longblob NOT NULL, PRIMARY KEY (id), KEY idx_diskfile (diskfile) )';
                if ( !db_query( $t_query ) ) {
                    return PLUGINS_PLUGINMANAGER_FAIL;
                }
            }
        } else {
            $p_msg = 'Database not connected';
            return PLUGINS_PLUGINMANAGER_FAIL;
        }
    } else if ( $p_step == 2 ) {
        $t_main_menu_custom_options = config_get( 'main_menu_custom_options', array() );
        $t_found = false;
        foreach( $t_main_menu_custom_options as $t_menu_link ) {
            if ( $t_menu_link[0] == 'plugins_releasemgt_link' ) {
                $t_found = true;
            }
        }
        if ( !$t_found ) {
            $t_main_menu_custom_options[] = array( 'plugins_releasemgt_link', VIEWER, 'plugins_page.php?plugin=releasemgt&display=releasemgt' );
        }
        config_set( 'main_menu_custom_options', $t_main_menu_custom_options );
    }
    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_upgrade_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_install_undo_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    if ( $p_step == 1 ) {
        $t_main_menu_custom_options = config_get( 'main_menu_custom_options', array() );
        for( $i=0; $i<count( $t_main_menu_custom_options ); $i++ ) {
            if ( $t_main_menu_custom_options[$i] == array( 'plugins_releasemgt_link', VIEWER, 'plugins_page.php?plugin=releasemgt&display=releasemgt' ) ) {
                unset( $t_main_menu_custom_options[$i] );
            }
        }
        config_set( 'main_menu_custom_options', $t_main_menu_custom_options );
    }
    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_upgrade_undo_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_uninstall_undo_plugin( &$p_msg, $p_step ) {
    $p_msg = '';
    
    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_uninstall_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    if ( $p_step == 1 ) {
        
    } else if ( $p_step == 2 ) {
        $t_main_menu_custom_options = config_get( 'main_menu_custom_options', array() );
        for( $i=0; $i<count( $t_main_menu_custom_options ); $i++ ) {
            if ( $t_main_menu_custom_options[$i] == array( 'plugins_releasemgt_link', VIEWER, 'plugins_page.php?plugin=releasemgt&display=releasemgt' ) ) {
                unset( $t_main_menu_custom_options[$i] );
            }
        }
        config_set( 'main_menu_custom_options', $t_main_menu_custom_options );
    }
    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_mantis_upgrade_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    return PLUGINS_PLUGINMANAGER_OK;
}

function plugins_releasemgt_mantis_repair_plugin( &$p_msg, $p_step ) {
    $p_msg = '';

    return PLUGINS_PLUGINMANAGER_OK;
}

?>
