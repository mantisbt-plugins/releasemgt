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

require_once( $t_core_path . 'file_api.php' );

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
    $result = db_query_bound( $query, array( (int)$p_file_id ), 1 );

    return db_result( $result );
}

function plugins_releasemgt_file_delete( $p_file_id ) {
    $t_upload_method = plugin_config_get( 'upload_method', UPLOAD_METHOD_DEFAULT );

    $t_filename = plugins_releasemgt_file_get_field( $p_file_id, 'filename' );
    $t_diskfile = plugins_releasemgt_file_get_field( $p_file_id, 'diskfile' );

    if( ( DISK == $t_upload_method ) || ( FTP == $t_upload_method ) ) {
        if ( FTP == $t_upload_method ) {
            $ftp = plugins_releasemgt_file_ftp_connect();
            file_ftp_delete( $ftp, $t_diskfile );
            file_ftp_disconnect( $ftp );
        }

        if ( file_exists( $t_diskfile ) ) {
            file_delete_local( $t_diskfile );
        }
    }

    $t_file_table = plugin_table ( 'file' );
    $query = "DELETE FROM $t_file_table
				WHERE id=" . db_param();
    $result = db_query_bound( $query, array( (int)$p_file_id ), 1 );
    return true;
}

function plugins_releasemgt_file_generate_unique_name( $p_seed, $p_filepath ) {
    do {
        $t_string = file_generate_name( $p_seed );
    } while ( !plugins_releasemgt_diskfile_is_name_unique( $t_string , $p_filepath ) );

    return $t_string;
}

function plugins_releasemgt_diskfile_is_name_unique( $p_name, $p_filepath ) {
    $t_file_table = plugin_table ( 'file' );

    $c_name = db_prepare_string( $p_filepath . $p_name );

    $query = "SELECT COUNT(*)
				  FROM $t_file_table
				  WHERE diskfile=" . db_param();
    $result = db_query_bound( $query, array( $c_name ) );
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
    $result = db_query_bound( $query, array( $c_name, (int)$p_project_id, (int)$p_version_id ) );
    $t_count = db_result( $result );

    return $t_count < 1;
}

function plugins_releasemgt_file_add( $p_tmp_file, $p_file_name, $p_file_type, $p_project_id, $p_version_id, $p_description, $p_file_error ) {
    if ( php_version_at_least( '4.2.0' ) ) {
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

    $t_file_hash = $p_version_id . '-' . $t_project_id;
    $t_disk_file_name = $t_file_path . plugins_releasemgt_file_generate_unique_name( $t_file_hash . '-' . $p_file_name, $t_file_path );
    $c_disk_file_name = db_prepare_string( $t_disk_file_name );

    $t_file_size = filesize( $p_tmp_file );
    if ( 0 == $t_file_size ) {
        trigger_error( ERROR_FILE_NO_UPLOAD_FAILURE, ERROR );
    }
    $t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
    if ( $t_file_size > $t_max_file_size ) {
        trigger_error( ERROR_FILE_TOO_BIG, ERROR );
    }

    $t_method = plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT );

    switch ( $t_method ) {
      case FTP:
      case DISK:
        file_ensure_valid_upload_path( $t_file_path );

        if ( !file_exists( $t_disk_file_name ) ) {
            if ( FTP == $t_method ) {
                $conn_id = plugins_releasemgt_file_ftp_connect();
                file_ftp_put( $conn_id, $t_disk_file_name, $p_tmp_file );
                file_ftp_disconnect( $conn_id );
            }

            if ( !move_uploaded_file( $p_tmp_file, $t_disk_file_name ) ) {
                trigger_error( FILE_MOVE_FAILED, ERROR );
            }

            chmod( $t_disk_file_name, 0644 );

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
    db_query_bound( $query, $param );
    $t_file_id = db_insert_id();
    return $t_file_id;
}

/**
 * Retaken function form print_api.php but it prints redirection message everytime
 * @param type $p_redirect_to
 */
function release_mgt_successful_redirect( $p_redirect_to ) {
    html_page_top( null, $p_redirect_to );
    echo '<br /><div class="center">';
    echo lang_get( 'operation_successful' ) . '<br />';
    print_bracket_link( $p_redirect_to, lang_get( 'proceed' ) );
    echo '</div>';
    html_page_bottom();
}

?>