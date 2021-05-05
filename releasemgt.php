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
class ReleasemgtPlugin extends MantisPlugin {
    function register() {
        $this->name = 'Release management';
        $this->description = 'Adding possibility to attach files to released versions.';
        $this->page = 'config';

        $this->version = '2.0.2';
        $this->requires = array(
            'MantisCore' => '2.0.0',
            );

        $this->author = 'Vincent DEBOUT, Jiri Hron, Igor Kozin';
        $this->contact = 'ikozin.src@gmail.com';
        $this->url = 'https://github.com/mantisbt-plugins/releasemgt';
    }

    function init() {
        $t_core = config_get_global('core_path' );
        $t_path = config_get_global('plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR;
        set_include_path(get_include_path() . PATH_SEPARATOR . $t_core . PATH_SEPARATOR . $t_path);
    }

    function hooks() {
            return array(
                    'EVENT_MENU_MAIN'        => 'showdownload_menu',
                    'EVENT_LAYOUT_RESOURCES' => 'resources',
            );
    }

    function showdownload_menu() {
            $t_page = plugin_page( 'releases', false, 'releasemgt' );
            $t_lang = plugin_lang_get( 'releases_link', 'releasemgt' );
            $t_menu_option = array(
                'title' => $t_lang,
                'url' => $t_page,
                'access_level' => plugin_config_get( 'view_threshold' ),
                'icon' => 'fa-download'
            );

            return array( $t_menu_option );
    }

    function schema() {
            return array(
                    array( 'CreateTableSQL', array( plugin_table( 'file' ), "
                            id                 I       NOTNULL UNSIGNED AUTOINCREMENT PRIMARY,
                            project_id         I       NOTNULL UNSIGNED,
                            version_id         I       NOTNULL UNSIGNED,
                            title              C(250)  NOTNULL DEFAULT '',
                            description        X       NOTNULL,
                            diskfile           C(250)  NOTNULL DEFAULT '',
                            filename           C(250)  NOTNULL DEFAULT '',
                            folder             C(250)  NOTNULL DEFAULT '',
                            filesize           I       NOTNULL DEFAULT '0',
                            file_type          C(250)  NOTNULL DEFAULT '',
                            date_added         T       NOTNULL DEFAULT '1970-01-01 00:00:01',
                            content            B       NOTNULL
                            " ) ),
                   // v.2.0.0
                   array( 'AddColumnSQL', array( plugin_table( 'file' ), "
                            enabled            L       NOTNULL DEFAULT 1
                            ",
                            array( "mysql" => "DEFAULT CHARSET=utf8" )
                        ) ),
                   array( 'AddColumnSQL', array( plugin_table( 'file' ), "
                            release_type       L       NOTNULL DEFAULT 0
                            ",
                            array( "mysql" => "DEFAULT CHARSET=utf8" )
                        ) ),
            );
    }

    function config() {
                return array(
                        'download_requires_login'   => true,
                        'view_threshold' => VIEWER,
                );
        }

    function resources($event) {
                return '<link rel="stylesheet" type="text/css" href="'.plugin_file("releasemgt.css").'"/>';
        }

}
