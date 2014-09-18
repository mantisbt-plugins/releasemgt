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

    require_once( 'releasemgt_api.php' );

    form_security_validate( 'plugin_Releasemgt_config_update' );

    auth_reauthenticate();
    access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

    $t_project_id = helper_get_current_project();

    $t_action = gpc_get_string( 'action', 'none' );
    if ( $t_action == 'update' ) {
        $t_upload_access_level = gpc_get_int( 'upload_access_level' );
        $t_upload_method = gpc_get_int( 'upload_method' );
        $t_disk_dir = gpc_get_string( 'disk_dir', PLUGINS_RELEASEMGT_DISK_DIR_DEFAULT );
        $t_ftp_server = gpc_get_string( 'ftp_server', PLUGINS_RELEASEMGT_FTP_SERVER_DEFAULT );
        $t_ftp_user = gpc_get_string( 'ftp_user', PLUGINS_RELEASEMGT_FTP_USER_DEFAULT );
        $t_ftp_pass = gpc_get_string( 'ftp_pass', PLUGINS_RELEASEMGT_FTP_PASS_DEFAULT );
        $t_file_number = gpc_get_int( 'file_number', PLUGINS_RELEASEMGT_FILE_NUMBER_DEFAULT );
        $t_notification_enable = gpc_get_bool( 'notification_enable' );
        $t_notify_handler = gpc_get_bool( 'notify_handler' );
        $t_notify_reporter = gpc_get_bool( 'notify_reporter' );
        $t_notify_email = gpc_get_string( 'notify_email', PLUGINS_RELEASEMGT_NOTIFY_EMAIL_DEFAULT );
        $t_email_subject = gpc_get_string( 'email_subject' );
        $t_email_template = gpc_get_string( 'email_template', PLUGINS_RELEASEMGT_EMAIL_TEMPLATE_DEFAULT );
        $t_download_requires_login = gpc_get_bool( 'download_requires_login');
        plugin_config_set( 'upload_threshold_level', $t_upload_access_level, NO_USER, $t_project_id );
        plugin_config_set( 'upload_method', $t_upload_method, NO_USER, $t_project_id );
        plugin_config_set( 'disk_dir', $t_disk_dir, NO_USER, $t_project_id );
        plugin_config_set( 'ftp_server', $t_ftp_server, NO_USER, $t_project_id );
        plugin_config_set( 'ftp_user', $t_ftp_user, NO_USER, $t_project_id );
        plugin_config_set( 'ftp_pass', $t_ftp_pass, NO_USER, $t_project_id );
        plugin_config_set( 'file_number', $t_file_number, NO_USER, $t_project_id );
        plugin_config_set( 'notification_enable', $t_notification_enable, NO_USER, $t_project_id );
        plugin_config_set( 'notify_handler', $t_notify_handler, NO_USER, $t_project_id );
        plugin_config_set( 'notify_reporter', $t_notify_reporter, NO_USER, $t_project_id );
        plugin_config_set( 'notify_email', $t_notify_email, NO_USER, $t_project_id );
        plugin_config_set( 'email_subject', $t_email_subject, NO_USER, $t_project_id );
        plugin_config_set( 'email_template', $t_email_template, NO_USER, $t_project_id );
        plugin_config_set( 'download_requires_login', $t_download_requires_login, NO_USER, $t_project_id );
    }
    if ( $t_action == 'delete' && $t_project_id != ALL_PROJECTS ) {
        plugin_config_delete( 'upload_threshold_level', NO_USER, $t_project_id );
        plugin_config_delete( 'upload_method', NO_USER, $t_project_id );
        plugin_config_delete( 'disk_dir', NO_USER, $t_project_id );
        plugin_config_delete( 'ftp_server', NO_USER, $t_project_id );
        plugin_config_delete( 'ftp_user', NO_USER, $t_project_id );
        plugin_config_delete( 'ftp_pass', NO_USER, $t_project_id );
        plugin_config_delete( 'file_number', NO_USER, $t_project_id );
        plugin_config_delete( 'notification_enable', NO_USER, $t_project_id );
        plugin_config_delete( 'notify_handler', NO_USER, $t_project_id );
        plugin_config_delete( 'notify_reporter', NO_USER, $t_project_id );
        plugin_config_delete( 'notify_email', NO_USER, $t_project_id );
        plugin_config_delete( 'email_subject', NO_USER, $t_project_id );
        plugin_config_delete( 'email_template', NO_USER, $t_project_id );
        plugin_config_delete( 'download_requires_login', NO_USER, $t_project_id );
    }

    form_security_purge( 'plugin_Releasemgt_config_update' );

    release_mgt_successful_redirect(plugin_page( 'config', true ));
