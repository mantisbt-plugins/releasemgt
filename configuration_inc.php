<?php

/**
 * ReleaseMgt plugin
 *
 *
 * Created: 2008-01-05
 * Last update: 2008-01-09
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright 
 * @author Vincent DEBOUT <deboutv@free.fr>
 */

if ( !ereg( 'plugins_page.php', $_SERVER['PHP_SELF'] ) ) {
    header( 'Location: ../../plugins_page.php' );
    exit();
}

auth_ensure_user_authenticated();

$t_current_user_id = auth_get_current_user_id();
$t_project_id = helper_get_current_project();

$t_action = gpc_get_string( 'action', 'none' );
if ( $t_action == 'update' ) {
    $t_upload_access_level = gpc_get_int( 'upload_access_level' );
    $t_upload_method = gpc_get_int( 'upload_method' );
    $t_disk_path = gpc_get_string( 'disk_path', PLUGINS_RELEASEMGT_DISK_PATH_DEFAULT );
    $t_ftp_server = gpc_get_string( 'ftp_server', PLUGINS_RELEASEMGT_FTP_SERVER_DEFAULT );
    $t_ftp_user = gpc_get_string( 'ftp_user', PLUGINS_RELEASEMGT_FTP_USER_DEFAULT );
    $t_ftp_pass = gpc_get_string( 'ftp_pass', PLUGINS_RELEASEMGT_FTP_PASS_DEFAULT );
    $t_notification_enable = gpc_get_bool( 'notification_enable' );
    $t_notify_handler = gpc_get_bool( 'notify_handler' );
    $t_notify_reporter = gpc_get_bool( 'notify_reporter' );
    $t_notify_email = gpc_get_string( 'notify_email', PLUGINS_RELEASEMGT_NOTIFY_EMAIL_DEFAULT );
    $t_email_template = gpc_get_string( 'email_template', PLUGINS_RELEASEMGT_EMAIL_TEMPLATE_DEFAULT );
    config_set( 'plugins_releasemgt_upload_threshold_level', $t_upload_access_level, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_upload_method', $t_upload_method, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_disk_path', $t_disk_path, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_ftp_server', $t_ftp_server, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_ftp_user', $t_ftp_user, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_ftp_pass', $t_ftp_pass, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_notification_enable', $t_notification_enable, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_notify_handler', $t_notify_handler, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_notify_reporter', $t_notify_reporter, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_notify_email', $t_notify_email, NO_USER, $t_project_id );
    config_set( 'plugins_releasemgt_email_template', $t_email_template, NO_USER, $t_project_id );
    header( 'Location: plugins_page.php?plugin=releasemgt' );
    exit();
}

html_page_top1( lang_get( 'plugins_releasemgt_configuration_page_title' ) );
html_page_top2();

?>

<br />
<div align="center">
<?php

  echo str_replace( '%%project%%', '<b>' . project_get_name( helper_get_current_project() ) . '</b>', lang_get( 'plugins_releasemgt_configuration_for_project' ) );

?>
  <br /><br />
  <form name="plugins_releasemgt" method="post" action="plugins_page.php">
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
    <input type="hidden" name="plugin" value="releasemgt" />
    <table class="width75" cellspacing="1">

      <!-- Upload access level -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_upload_access_level' ); ?>
        </td>
        <td width="70%">
          <select name="upload_access_level">
<?php print_enum_string_option_list( 'access_levels', config_get( 'plugins_releasemgt_upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ) ); ?>
          </select>
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- Upload method -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_upload_method' ); ?>
        </td>
        <td width="70%">
          <select name="upload_method">
            <option value="<?php echo DATABASE ?>"<?php if ( config_get( 'plugins_releasemgt_upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) == DATABASE ) echo ' selected="selected"'; ?>><?php echo lang_get( 'plugins_releasemgt_method_database' ) ?></option>
            <option value="<?php echo DISK ?>"<?php if ( config_get( 'plugins_releasemgt_upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) == DISK ) echo ' selected="selected"'; ?>><?php echo lang_get( 'plugins_releasemgt_method_disk' ) ?></option>
            <option value="<?php echo FTP ?>"<?php if ( config_get( 'plugins_releasemgt_upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) == FTP ) echo ' selected="selected"'; ?>><?php echo lang_get( 'plugins_releasemgt_method_ftp' ) ?></option>
          </select>
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- Disk parameter -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_disk_path' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="disk_path" size="30" value="<?php echo config_get( 'plugins_releasemgt_disk_path', PLUGINS_RELEASEMGT_DISK_PATH_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- FTP parameters: server -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_ftp_server' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="ftp_server" size="30" value="<?php echo config_get( 'plugins_releasemgt_ftp_server', PLUGINS_RELEASEMGT_FTP_SERVER_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- FTP parameters: user -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_ftp_user' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="ftp_user" value="<?php echo config_get( 'plugins_releasemgt_ftp_user', PLUGINS_RELEASEMGT_FTP_USER_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- FTP parameters: pass -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_ftp_pass' ); ?>
        </td>
        <td width="70%">
          <input type="password" name="ftp_pass" value="<?php echo config_get( 'plugins_releasemgt_ftp_pass', PLUGINS_RELEASEMGT_FTP_PASS_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- Notification enable -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <?php echo lang_get( 'plugins_releasemgt_notification_enable' ); ?>
        </td>
        <td width="70%">
          <input type="checkbox" name="notification_enable"<?php if ( config_get( 'plugins_releasemgt_notification_enable', PLUGINS_RELEASEMGT_NOTIFICATION_ENABLE_DEFAULT ) == ON ) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- Notify handler -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <?php echo lang_get( 'plugins_releasemgt_notify_handler' ); ?>
        </td>
        <td width="70%">
          <input type="checkbox" name="notify_handler"<?php if ( config_get( 'plugins_releasemgt_notify_handler', PLUGINS_RELEASEMGT_NOTIFY_HANDLER_DEFAULT ) == ON ) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- Notify reporter -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <?php echo lang_get( 'plugins_releasemgt_notify_reporter' ); ?>
        </td>
        <td width="70%">
          <input type="checkbox" name="notify_reporter"<?php if ( config_get( 'plugins_releasemgt_notify_reporter', PLUGINS_RELEASEMGT_NOTIFY_REPORTER_DEFAULT ) == ON ) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- Notify email -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <?php echo lang_get( 'plugins_releasemgt_notify_email' ); ?>
        </td>
        <td width="70%">
          <textarea name="notify_email"><?php echo config_get( 'plugins_releasemgt_notify_email', PLUGINS_RELEASEMGT_NOTIFY_EMAIL_DEFAULT ) ?></textarea>
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- Email template -->
      <tr <?php echo helper_alternate_class() ?>>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo lang_get( 'plugins_releasemgt_email_template' ); ?>
        </td>
        <td width="70%">
          <select name="email_template">
<?php

$t_selected = config_get( 'plugins_releasemgt_email_template', PLUGINS_RELEASEMGT_EMAIL_TEMPLATE_DEFAULT );

$t_dir = dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
$t_dirs = array();
$t_handle = opendir( $t_dir );
while( ( $t_file = readdir( $t_handle ) ) !== false ) {
    if ( preg_match( '/^[a-z0-9-_]+$/i', $t_file ) ) {
        $t_dirs[] = $t_file;
    }
}
closedir( $t_handle );
sort( $t_dirs );
foreach( $t_dirs as $t_dir ) {
    echo '<option value="' . $t_dir . '"';
    if ( $t_selected == $t_dir ) {
        echo ' selected="selected"';
    }
    echo '>' . $t_dir . '</option>' . "\n";
}

?>
          </select>
        </td>
      </tr>

      <!-- Submit Button -->
      <tr>
        <td class="left">
          <span class="required"> * <?php echo lang_get( 'required' ) ?></span>
        </td>
        <td class="center">
          <input tabindex="4" type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" />
        </td>
      </tr>

    </table>
  </form>

</div>

<?php 

html_page_bottom1( __FILE__ );

?>