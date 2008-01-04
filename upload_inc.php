<?php

/**
 * ReleaseMgt plugin
 *
 *
 * Created: 2008-01-05
 * Last update: 2008-02-04
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright 
 * @author Vincent DEBOUT <deboutv@free.fr>
 */

if ( !ereg( 'plugins_page.php', $_SERVER['PHP_SELF'] ) ) {
    header( 'Location: ../../plugins_page.php' );
    exit();
}

$t_file = gpc_get_file( 'file' );
$t_version = gpc_get_int( 'release', 0 );
$t_description = gpc_get_string( 'description', '' );

$t_current_user_id = auth_get_current_user_id();
$t_project_id = helper_get_current_project();

if ( user_get_access_level( $t_current_user_id ) < config_get( 'plugins_releasemgt_upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ) ) {
    access_denied();
}

$t_file_error = ( isset( $t_file['error'] ) ) ? $t_file['error'] : 0;
$t_file_id = plugins_releasemgt_file_add( $t_file['tmp_name'], $t_file['name'], $t_file['type'], $t_project_id, $t_version, $t_description, $t_file_error );

$t_redirect_url = 'plugins_page.php?plugin=releasemgt&display=releasemgt';

if ( config_get( 'plugins_releasemgt_notification_enable', PLUGINS_RELEASEMGT_NOTIFICATION_ENABLE_DEFAULT ) == ON ) {
    $t_subject = lang_get( 'plugins_releasemgt_email_subject' );
    $t_template_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . config_get( 'plugins_releasemgt_email_template', PLUGINS_RELEASEMGT_EMAIL_TEMPLATE_DEFAULT ) . DIRECTORY_SEPARATOR;

    $t_template = array();
    $t_template['file_name'] = $t_file['name'];
    $t_template['file_description'] = $t_description;
    $t_template['file_html_description'] = string_display_links( $t_description );
    $t_template['file_url'] = config_get( 'path' ) . 'plugins_page.php?plugin=releasemgt&display=download&id=' . $t_file_id;
    $t_template['project_id'] = $t_project_id;
    $t_template['project_name'] = project_get_name( $t_project_id );
    $t_template['version_id'] = $t_version;
    if ( $t_version != 0 ) {
        $t_template['version_name'] = version_get_field( $t_version, 'name' );
        $t_template['version_description'] = version_get_field( $t_version, 'description' );
        $t_template['version_date_order'] = version_get_field( $t_version, 'date_order' );
    } else {
        $t_template['version_name'] = '';
        $t_template['version_description'] = '';
        $t_template['version_date_order'] = '';
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
    if ( config_get( 'plugins_releasemgt_notify_handler', PLUGINS_RELEASEMGT_NOTIFY_HANDLER_DEFAULT ) == ON ) {
        $t_user_list = project_get_all_user_rows( $t_project_id, config_get( 'handle_bug_threshold' ) );
        foreach( $t_user_list as $t_user ) {
            $t_id_list[] = $t_user['id'];
        }
    }
    // Get reporter
    if ( config_get( 'plugins_releasemgt_notify_reporter', PLUGINS_RELEASEMGT_NOTIFY_REPORTER_DEFAULT ) == ON ) {
        if ( $t_version == 0 ) {
            $t_query = 'SELECT reporter_id FROM ' . config_get( 'mantis_bug_table' ) . ' WHERE project_id=' . db_prepare_int( $t_project_id ) . ' AND fixed_in_version=\'\'';
        } else {
            $t_query = 'SELECT reporter_id FROM ' . config_get( 'mantis_bug_table' ) . ' WHERE project_id=' . db_prepare_int( $t_project_id ) . ' AND fixed_in_version=\'' . db_prepare_string( version_get_field( $t_version, 'version' ) ) . '\'';
        }
        $t_result = db_query( $t_query );
        while( $t_row = db_fetch_array( $t_result ) ) {
            $t_id_list[] = $t_row['reporter_id'];
        }
    }
    for( $i=0; $i<count( $t_id_list ); $i++ ) {
        $t_id_list[$i] = user_get_email( $t_id_list[$i] );
    }

    // Add users
    $t_emails = explode( ',', config_get( 'plugins_releasemgt_notify_email', PLUGINS_RELEASEMGT_NOTIFY_EMAIL_DEFAULT ) );
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
            $t_email_data->metadata['charset'] = lang_get( 'charset', lang_get_current() );
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