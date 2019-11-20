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

function releasemgt_plugin_send_email( $p_project_id, $p_version, $p_files, $p_descriptions, $p_files_id )
{

    if ( plugin_config_get( 'notification_enable', PLUGINS_RELEASEMGT_NOTIFICATION_ENABLE_DEFAULT ) != ON )
	return;

    $p_files_count = count( $p_files );
    $t_subject = plugin_config_get( 'email_subject', PLUGINS_RELEASEMGT_EMAIL_SUBJECT_DEFAULT );
    $t_subject_replace = array( '*P' => '*p', '*C' => '*c', '*V' => '*v', 
        '*p' => project_get_name( $p_project_id ), 
        '*v' => ($p_version == PLUGINS_RELEASEMGT_NO_RELEASE_VERSION ? plugin_lang_get( 'no_release_version_name' ) : version_get_field( $p_version, 'version' )), 
        '*c' => $p_files_count, '**' => '*' );
    foreach( $t_subject_replace as $t_key => $t_value ) {
        $t_subject = str_replace( $t_key, $t_value, $t_subject );
    }
    $t_selected = plugin_config_get( 'email_template', PLUGINS_RELEASEMGT_EMAIL_TEMPLATE_DEFAULT );
    $t_template_dir = config_get_global('plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . $t_selected . DIRECTORY_SEPARATOR;

    $t_template = array();
    $t_template['files'] = array();
    $t_template['files_count'] = $p_files_count;
    for( $i=0; $i<$p_files_count; $i++ ) {
        $t_template['files'][$i] = array();
        $t_template['files'][$i]['file_name'] = $p_files[$i]['name'];
        $t_template['files'][$i]['file_description'] = $p_descriptions[$i];
        $t_template['files'][$i]['file_html_description'] = string_display_links( $p_descriptions[$i] );
        $t_template['files'][$i]['file_url'] = config_get( 'path' ) . plugin_page( 'download' , true) . '&id=' . $p_files_id[$i];
        $t_template['files'][$i]['file_size'] = number_format( $p_files[$i]['size'] );
        $t_template['files'][$i]['file_date'] = date( config_get( 'normal_date_format' ), plugins_releasemgt_file_get_field( $p_files_id[$i], 'date_added'  ) );
    }
    $t_template['project_id'] = $p_project_id;
    $t_template['project_name'] = project_get_name( $p_project_id );
    $t_template['version_id'] = $p_version;
    if ( $p_version != 0 ) {
        $t_template['version_name'] = version_get_field( $p_version, 'version' );
        $t_template['version_description'] = version_get_field( $p_version, 'description' );
        $t_template['version_date_order'] = version_get_field( $p_version, 'date_order' );
        $t_template['version_date'] = date( config_get( 'normal_date_format' ), $t_template['version_date_order'] );
    } else {
        $t_template['version_name'] = '';
        $t_template['version_description'] = '';
        $t_template['version_date_order'] = '';
        $t_template['version_date'] = '';
    }
    $t_template['user_id'] = $t_current_user_id;
    $t_template['user_name'] = user_get_name( $t_current_user_id );
    $t_template['user_realname'] = user_get_realname( $t_current_user_id );
    $t_template['user_email'] = user_get_email( $t_current_user_id );

    $t_template_file = $t_template_dir . 'text_inc.php';
    ob_start();
    if ( file_exists( $t_template_file ) ) {
        include( $t_template_file );
    } else {
        echo 'ERROR: Contact your Mantis administrator';
    }
    $t_message = ob_get_contents();
    ob_end_clean();
    $t_template_file = $t_template_dir . 'html_inc.php';
    ob_start();
    if ( file_exists( $t_template_file ) ) {
        include( $t_template_file );
    } else {
        echo 'ERROR: Contact your Mantis administrator';
    }
    $t_html_message = ob_get_contents();
    ob_end_clean();

    $t_id_list = array();
    if ( plugin_config_get( 'notify_handler', PLUGINS_RELEASEMGT_NOTIFY_HANDLER_DEFAULT ) == ON ) {
        $t_user_list = project_get_all_user_rows( $p_project_id, config_get( 'handle_bug_threshold' ) );
        foreach( $t_user_list as $t_user ) {
            $t_id_list[] = $t_user['id'];
        }
    }
    // Get reporter
    if ( plugin_config_get( 'notify_reporter', PLUGINS_RELEASEMGT_NOTIFY_REPORTER_DEFAULT ) == ON ) {
		$t_query = 'SELECT reporter_id
			FROM ' . db_get_table( 'mantis_bug_table' ) . '
			WHERE project_id=' . db_param() . ' AND fixed_in_version=' . db_param();
        if ( $p_version == 0 ) {
			$c_version = '';
        } else {
			$c_version = version_get_field( $p_version, 'version' );
        }
        $t_result = db_query( $t_query, array( (int)$p_project_id, db_prepare_string( $c_version ) ) );
        while( $t_row = db_fetch_array( $t_result ) ) {
            $t_id_list[] = $t_row['reporter_id'];
        }
    }
    for( $i=0; $i<count( $t_id_list ); $i++ ) {
        $t_id_list[$i] = user_get_email( $t_id_list[$i] );
    }

    // Add users
    $t_emails = explode( ',', plugin_config_get( 'notify_email', PLUGINS_RELEASEMGT_NOTIFY_EMAIL_DEFAULT ) );
    foreach( $t_emails as $t_email ) {
        if ( trim( $t_email ) != '' ) {
            $t_id_list[] = trim( $t_email );
        }
    }
    $t_email_ids = array_unique( $t_id_list );

    if ( defined( 'MANTIS_VERSION' ) ) {
        $t_mantis_version = MANTIS_VERSION;
    } else {
        $t_mantis_version = config_get( 'mantis_version' );
    }
    if ( version_compare( $t_mantis_version, '1.1.0a2', '>=' ) ) {
        foreach( $t_email_ids as $t_email ) {
            $t_recipient = trim( $t_email );
            $t_subject = string_email( trim( $t_subject ) );
            $t_message = string_email_links( trim( $t_message ) );
            $t_email_data = new EmailData;

            $t_email_data->email = $t_recipient;
            $t_email_data->subject = $t_subject;
            $t_email_data->body = $t_message;
            $t_email_data->metadata = array();
            $t_email_data->metadata['headers'] = array( 'X-Mantis' => 'ReleaseMgt' );
            $t_email_data->metadata['priority'] = config_get( 'mail_priority' );
            $t_email_data->metadata['charset'] = 'utf-8';
            $t_email_data->metadata['plugins_htmlmail_html_message'] = base64_encode( $t_html_message );
            email_queue_add( $t_email_data );
        }
        if ( OFF == config_get( 'email_send_using_cronjob' ) ) {
            email_send_all();
        }
    } else {
        foreach( $t_email_ids as $t_email ) {
            email_send( $t_email, $t_subject, $t_message );
        }
    }
}