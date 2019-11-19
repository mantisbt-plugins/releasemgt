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
 */

require_once( 'constant_api.php' );
require_once( 'file_api.php' );

function releasemgt_plugin_page_path( $p_page_name )
{
    return plugin_route_group() . '/pages/' . $p_page_name . '.php';
}

function releasemgt_plugin_page_url( $p_page_name )
{
    return helper_mantis_url( releasemgt_plugin_page_path( $p_page_name ) );
}
/*
*/
function releasemgt_plugin_page_title( $p_project_name, $p_page_title )
{
    echo '<div class="row">';
    echo '<div class="col-md-12 col-xs-12">';
    echo '<div class="page-header">';
    echo '<h1><strong>' . $p_project_name, '</strong> - ', $p_page_title  . '</h1>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}

function releasemgt_plugin_release_title( $p_release_title, $p_release_version )
{
    $t_block_id = 'release_' . $p_release_version;
    $t_collapse_block = is_collapsed( $t_block_id );
    $t_block_css = $t_collapse_block ? 'collapsed' : '';
    $t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

    echo '<div>';
    echo '<div id="' . $t_block_id . '" class="widget-box widget-color-blue2 ' . $t_block_css . '">';
    echo '<div class="widget-header widget-header-small">';
    echo '<h4 class="widget-title lighter">';
    echo '<i class="ace-icon fa fa-retweet"></i>';
    echo $p_release_title, lang_get( 'word_separator' );
    echo '</h4>';
//      echo '<div class="widget-toolbar">';
//      echo '<a data-action="collapse" href="#">';
//      echo '<i class="1 ace-icon fa ' . $t_block_icon . ' bigger-125"></i>';
//      echo '</a>';
//      echo '</div>';
    echo '</div>';
    echo '</div>';

/*
        echo '<div class="widget-body">';
        echo '<div class="widget-toolbox padding-8 clearfix">';
        echo '<div class="pull-left"><i class="fa fa-calendar-o fa-lg"> </i> ' . $t_release_date . '</div>';
        echo '<div class="btn-toolbar pull-right">';
        echo '<a class="btn btn-xs btn-primary btn-white btn-round" ';
        echo 'href="view_all_set.php?type=1&temporary=y&' . FILTER_PROPERTY_PROJECT_ID . '=' . $t_project_id .
                 '&' . filter_encode_field_and_value( FILTER_PROPERTY_FIXED_IN_VERSION, $t_version_name ) .
                 '&' . FILTER_PROPERTY_HIDE_STATUS . '=' . META_FILTER_NONE . '">';
        echo lang_get( 'view_bugs_link' );
        echo '<a class="btn btn-xs btn-primary btn-white btn-round" href="changelog_page.php?version_id=' . $p_version_id . '">' . string_display_line( $t_version_name ) . '</a>';
        echo '<a class="btn btn-xs btn-primary btn-white btn-round" href="changelog_page.php?project_id=' . $t_project_id . '">' . string_display_line( $t_project_name ) . '</a>';
        echo '</a>';
        echo '</div>';

        echo '</div>';
*/
        echo '<div class="widget-main">';

}

function releasemgt_plugin_section_title( $p_title, $p_fa_icon, $p_block_id )
{
    $t_block_id = $p_block_id;
    $t_collapse_block = is_collapsed( $t_block_id );
    $t_block_css = $t_collapse_block ? 'collapsed' : '';
    $t_block_icon = $t_collapse_block ? 'fa-chevron-down' : 'fa-chevron-up';

    echo '<div>';
    echo '<div id="' . $t_block_id . '" class="widget-box widget-color-blue2 ' . $t_block_css . '">';
    echo '<div class="widget-header widget-header-small">';
    echo '<h4 class="widget-title lighter">';
    echo '<i class="ace-icon fa ' . $p_fa_icon . '"></i>';
    echo $p_title, lang_get( 'word_separator' );
    echo '</h4>';
//      echo '<div class="widget-toolbar">';
//      echo '<a data-action="collapse" href="#">';
//      echo '<i class="1 ace-icon fa ' . $t_block_icon . ' bigger-125"></i>';
//      echo '</a>';
//      echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<div class="widget-main">';
}

function releasemgt_max_upload_size()
{
        $t_max_sizes = array(
            'max_file_ini_upload' => ini_get_number( 'upload_max_filesize' ),
            'max_file_ini_post'   => ini_get_number( 'post_max_size' ),
//            'max_file_mantis_cfg' => config_get( 'max_file_size' ),
        );
        $t_max_key = '';
        $t_max_size = 0x7FFFFFFF;
        foreach( $t_max_sizes as $key => $size )
        {
            if( $size < $t_max_size )
            {
                $t_max_size = $size;
                $t_max_key  = $key;
            }
        }
        return array( $t_max_size, $t_max_key );
}


/**
 *
 * @todo Not yet converted to the new plugin system
 */
function plugins_releasemgt_file_ftp_connect() {
    $conn_id = ftp_connect( config_get( 'plugins_releasemgt_ftp_server', PLUGINS_RELEASEMGT_FTP_SERVER_DEFAULT ) );
    $login_result = ftp_login( $conn_id, config_get( 'plugins_releasemgt_ftp_user', PLUGINS_RELEASEMGT_FTP_USER_DEFAULT ), config_get( 'plugins_releasemgt_ftp_pass', PLUGINS_RELEASEMGT_FTP_USER_DEFAULT ) );

    if ( ( !$conn_id ) || ( !$login_result ) ) {
        trigger_error( ERROR_FTP_CONNECT_ERROR, ERROR );
    }

    return $conn_id;
}

function plugins_releasemgt_file_get_field( $p_file_id, $p_field_name ) {
    $c_field_name = db_prepare_string( $p_field_name );
    $t_file_table = plugin_table('file');

    $query = "SELECT $c_field_name
                                  FROM $t_file_table
                                  WHERE id=" . db_param();
    $result = db_query( $query, array( (int)$p_file_id ), 1 );

    return db_result( $result );
}

function plugins_releasemgt_file_delete( $p_file_id ) {
    $t_upload_method = plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT );

    $t_filename = plugins_releasemgt_file_get_field( $p_file_id, 'filename' );
    $t_diskfile = plugins_releasemgt_file_get_field( $p_file_id, 'diskfile' );

    if( ( DISK == $t_upload_method ) || ( FTP == $t_upload_method ) ) {
        /* IK! FTP can't be used with DISK
        if ( FTP == $t_upload_method ) {
            $ftp = plugins_releasemgt_file_ftp_connect();
            file_ftp_delete( $ftp, $t_diskfile );
            file_ftp_disconnect( $ftp );
        }
        */

        if ( file_exists( $t_diskfile ) ) {
            file_delete_local( $t_diskfile );
        }
    }

    $t_file_table = plugin_table ( 'file' );
    $query = "DELETE FROM $t_file_table
                                WHERE id=" . db_param();
    $result = db_query( $query, array( (int)$p_file_id ), 1 );
    return true;
}

function plugins_releasemgt_file_enable( $p_file_id, $p_enable ) {
    $t_file_table = plugin_table ( 'file' );
    $query = "UPDATE $t_file_table"
           . ' SET enabled=' . db_param()
           . ' WHERE id=' . db_param();
    $result = db_query( $query, array( (int)($p_enable ? 1 : 0), (int)$p_file_id, ), 1 );
    return true;
}

function plugins_releasemgt_file_generate_unique_name( $p_seed, $p_filepath ) {
    $t_string = $p_seed;
    while ( !plugins_releasemgt_diskfile_is_name_unique( $t_string , $p_filepath ) )
    {
        $t_string = file_generate_unique_name( $p_seed );
    }
    return $t_string;
}

function plugins_releasemgt_diskfile_is_name_unique( $p_name, $p_filepath ) {
    $t_file_table = plugin_table ( 'file' );

    $c_name = db_prepare_string( $p_filepath . $p_name );

    $query = "SELECT COUNT(*)
                                  FROM $t_file_table
                                  WHERE diskfile=" . db_param();
    $result = db_query( $query, array( $c_name ) );
    $t_count = db_result( $result );

    return $t_count < 1;
}

function plugins_releasemgt_file_is_name_unique( $p_name, $p_project_id, $p_version_id ) {
    $t_file_table = plugin_table( 'file' );

    $c_name = db_prepare_string( $p_name );
    $c_project_id = db_prepare_int( $p_project_id );
    $c_version_id = db_prepare_int( $p_version_id );

    $query = "SELECT COUNT(*)
                                  FROM $t_file_table
                                  WHERE filename=" . db_param() . " AND project_id=" . db_param() . " AND version_id=" . db_param();
    $result = db_query( $query, array( $c_name, (int)$p_project_id, (int)$p_version_id ) );
    $t_count = db_result( $result );

    return $t_count < 1;
}

function plugins_releasemgt_file_add( $p_tmp_file, $p_file_name, $p_file_type, $p_project_id, $p_version_id, $p_description, $p_file_error ) {
    if ( version_compare(PHP_VERSION, '4.2.0') >= 0 ) {
        switch ( (int) $p_file_error ) {
          case UPLOAD_ERR_INI_SIZE:
          case UPLOAD_ERR_FORM_SIZE:
            trigger_error( ERROR_FILE_TOO_BIG, ERROR );
            break;
          case UPLOAD_ERR_PARTIAL:
          case UPLOAD_ERR_NO_FILE:
            trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
            break;
          default:
            break;
        }
    }

    if ( ( '' == $p_tmp_file ) || ( '' == $p_file_name ) ) {
        trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
    }
    if ( !is_readable( $p_tmp_file ) ) {
        trigger_error( ERROR_UPLOAD_FAILURE, ERROR );
    }

    if ( !plugins_releasemgt_file_is_name_unique( $p_file_name, $p_project_id, $p_version_id ) ) {
        trigger_error( ERROR_DUPLICATE_FILE, ERROR );
    }

    $c_file_type = db_prepare_string( $p_file_type );
    $c_title = db_prepare_string( $p_file_name );
    $c_desc = db_prepare_string( $p_description );

    $t_file_path = dirname( plugin_config_get( 'disk_dir', PLUGINS_RELEASEMGT_DISK_DIR_DEFAULT ) . DIRECTORY_SEPARATOR . '.' ) . DIRECTORY_SEPARATOR;

    $c_file_path = db_prepare_string( $t_file_path );
    $c_new_file_name = db_prepare_string( $p_file_name );

    $t_file_hash = $p_version_id . '-' . $p_project_id;
    $t_disk_file_name = $t_file_path . plugins_releasemgt_file_generate_unique_name( $t_file_hash . '-' . $p_file_name, $t_file_path );
    $c_disk_file_name = db_prepare_string( $t_disk_file_name );

    $t_file_size = filesize( $p_tmp_file );
    if ( 0 == $t_file_size ) {
        trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
    }
    //$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
    $t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ) );
    if ( $t_file_size > $t_max_file_size ) {
        trigger_error( ERROR_FILE_TOO_BIG, ERROR );
    }

    $t_method = plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT );

//echo "DBG: -3, $t_file_path, method=$t_method<BR>\n";
    switch ( $t_method ) {
      //case FTP:
      case DISK:
        file_ensure_valid_upload_path( $t_file_path );
//echo "DBG: -2, $t_file_path<BR>\n";

        if ( !file_exists( $t_disk_file_name ) ) {
//echo "DBG: -1, $t_file_path<BR>\n";
/* IK. FTP can't be used here because in Mantis FTP === DISK
            if ( FTP == $t_method ) {
echo "DBG: 0:0, $t_method, FTP<BR>\n";
                $conn_id = plugins_releasemgt_file_ftp_connect();
                file_ftp_put( $conn_id, $t_disk_file_name, $p_tmp_file );
                file_ftp_disconnect( $conn_id );
            }
*/
//echo "DBG: 1<BR>\n";
            if ( !move_uploaded_file( $p_tmp_file, $t_disk_file_name ) ) {
                trigger_error( FILE_MOVE_FAILED, ERROR );
            }
//echo "DBG: 2<BR>\n";

            chmod( $t_disk_file_name, 0644 );
//echo "DBG: 3<BR>\n";

            $c_content = '';
        } else {
            trigger_error( ERROR_FILE_DUPLICATE, ERROR );
        }
        break;
      case DATABASE:
        $c_content = db_prepare_binary_string( fread( fopen( $p_tmp_file, 'rb' ), $t_file_size ) );
        break;
      default:
        trigger_error( ERROR_GENERIC, ERROR );
    }

    $t_file_table = plugin_table ( 'file' );
    $query = "INSERT INTO $t_file_table
                                (project_id, version_id, title, description,
                                 diskfile, filename, folder, filesize,
                                 file_type, date_added, content)
                          VALUES
                                (" . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . ",
                                 " . db_param() . ", " . db_param() . ", " . db_param() . ", " . db_param() . ",
                                 " . db_param() . ", " . db_param() . ", " . db_param() . ")";
        $param = array(
                (int)$p_project_id, (int)$p_version_id, $c_title, $c_desc, $c_disk_file_name,
                $c_new_file_name, $c_file_path, (int)$t_file_size, $c_file_type, date("Y-m-d H:i:s"),
                $c_content
        );
    db_query( $query, $param );
    $t_file_id = db_insert_id( $t_file_table );
    return $t_file_id;
}

/**
 * Retaken function form print_api.php but it prints redirection message everytime
 * @param type $p_redirect_to
 */
function release_mgt_successful_redirect( $p_redirect_to ) {
    print_successful_redirect( plugin_page( $p_redirect_to, true ) );
}
