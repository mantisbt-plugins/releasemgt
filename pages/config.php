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
require_once( 'releasemgt_api.php' );

auth_reauthenticate(  );
access_ensure_global_level( config_get( 'manage_plugin_threshold' ) );

layout_page_header( plugin_lang_get( 'configuration_page_title' ) );

layout_page_begin( 'manage_overview_page.php' );
print_manage_menu( 'manage_plugin_page.php' );

$t_project_id = helper_get_current_project();

?>

<br />
<!-- div align="center" -->
<?php

  //echo str_replace( '%%project%%', '<b>' . project_get_name( helper_get_current_project() ) . '</b>',plugin_lang_get( 'configuration_for_project' ) );
  releasemgt_plugin_section_title( 
      str_replace( '%%project%%', '<b>' . project_get_name( helper_get_current_project() ) . '</b>', plugin_lang_get( 'configuration_for_project' ) ), 
      'fa-file-o',
      'releasemgt_config' 
  );
?>
  <br /><br />
  <form name="plugins_releasemgt" method="post" action="<?php echo plugin_page( 'config_update' ) ?>">
    <?php echo form_security_field( 'plugin_Releasemgt_config_update' ) ?>
    <input type="hidden" name="action" value="update" />
    <input type="hidden" name="project_id" value="<?php echo $t_project_id ?>" />
    <input type="hidden" name="plugin" value="releasemgt" />
<!--    <table class="width75" cellspacing="1"> -->
    <table class="width100 table table-striped table-bordered table-condensed" cellspacing="1">

      <!-- Upload access level -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'upload_access_level' ); ?>
        </td>
        <td width="70%">
          <select name="upload_access_level">
            <?php print_enum_string_option_list( 'access_levels', plugin_config_get( 'upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT ) ); ?>
          </select>
        </td>
      </tr>

      <!-- Download access level -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'download_requires_login' ); ?>
        </td>
        <td width="70%">
            <input type="checkbox" name="download_requires_login"<?php if ( plugin_config_get( 'download_requires_login' )) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- file number -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'file_count' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="file_number" value="<?php echo plugin_config_get( 'file_number', PLUGINS_RELEASEMGT_FILE_NUMBER_DEFAULT ); ?>" size="3" maxlength="1" />
        </td>
      </tr>

      <!-- Upload method -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'upload_method' ); ?>
          <br /><span class="small">
<?php
        $t_max_size = releasemgt_max_upload_size();
        echo lang_get( 'max_file_size_label' ) . ' '. number_format( $t_max_size[0]/1000 ) . '&nbsp;kB (limited by ' . plugin_lang_get( $t_max_size[1] ) . ')';
?>
          </span>
        </td>
        <td width="70%">
          <select name="upload_method">
            <?php
             /**
              * @todo Database file storage is not yet converted - so function is disabled
                <option value="<?php echo DATABASE ?>"<?php if ( plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) == DATABASE ) echo ' selected="selected"'; ?>><?php echo plugin_lang_get( 'method_database' ) ?></option>
              */
            ?>
            <option value="<?php echo DISK ?>"<?php if ( plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) == DISK ) echo ' selected="selected"'; ?>><?php echo plugin_lang_get( 'method_disk' ) ?></option>
            <?php
             /**
              * @todo FTP file storage is not yet converted - so function is disabled
                <option value="<?php echo FTP ?>"<?php if ( plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT ) == FTP ) echo ' selected="selected"'; ?>><?php echo plugin_lang_get( 'method_ftp' ) ?></option>
              */
            ?>
          </select>
        </td>
      </tr>


      <!-- Disk parameter -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'disk_path' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="disk_dir" size="60" value="<?php echo plugin_config_get( 'disk_dir', PLUGINS_RELEASEMGT_DISK_DIR_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <?php
      /**
       * @todo FTP file storage is not yet converted - so function is disabled
      <!-- FTP parameters: server -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'ftp_server' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="ftp_server" size="30" value="<?php echo plugin_config_get( 'ftp_server', PLUGINS_RELEASEMGT_FTP_SERVER_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- FTP parameters: user -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'ftp_user' ); ?>
        </td>
        <td width="70%">
          <input type="text" name="ftp_user" value="<?php echo plugin_config_get( 'ftp_user', PLUGINS_RELEASEMGT_FTP_USER_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- FTP parameters: pass -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'ftp_pass' ); ?>
        </td>
        <td width="70%">
          <input type="password" name="ftp_pass" value="<?php echo plugin_config_get( 'ftp_pass', PLUGINS_RELEASEMGT_FTP_PASS_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>
       *
       */
      ?>

      <!-- Notification enable -->
      <tr>
        <td class="category" width="30%">
          <?php echo plugin_lang_get( 'notification_enable' ); ?>
        </td>
        <td width="70%">
          <input type="checkbox" name="notification_enable"<?php if ( plugin_config_get( 'notification_enable', PLUGINS_RELEASEMGT_NOTIFICATION_ENABLE_DEFAULT ) == ON ) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- Notify handler -->
      <tr>
        <td class="category" width="30%">
          <?php echo plugin_lang_get( 'notify_handler' ); ?>
        </td>
        <td width="70%">
          <input type="checkbox" name="notify_handler"<?php if ( plugin_config_get( 'notify_handler', PLUGINS_RELEASEMGT_NOTIFY_HANDLER_DEFAULT ) == ON ) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- Notify reporter -->
      <tr>
        <td class="category" width="30%">
          <?php echo plugin_lang_get( 'notify_reporter' ); ?>
        </td>
        <td width="70%">
          <input type="checkbox" name="notify_reporter"<?php if ( plugin_config_get( 'notify_reporter', PLUGINS_RELEASEMGT_NOTIFY_REPORTER_DEFAULT ) == ON ) echo ' checked="checked"' ?> />
        </td>
      </tr>

      <!-- Notify email -->
      <tr>
        <td class="category" width="30%">
          <?php echo plugin_lang_get( 'notify_email' ); ?>
        </td>
        <td width="70%">
          <textarea rows="2" cols="60" name="notify_email"><?php echo plugin_config_get( 'notify_email', PLUGINS_RELEASEMGT_NOTIFY_EMAIL_DEFAULT ) ?></textarea>
        </td>
      </tr>

      <!-- spacer -->
      <tr>
        <td class="spacer" colspan="2">&nbsp;</td>
      </tr>

      <!-- Email subject -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'email_subject' ); ?> <?php //plugins_helplink_print_link( 'email_subject_help' ) ?>
        </td>
        <td width="70%">
          <input type="text" name="email_subject" size="60" value="<?php echo plugin_config_get( 'email_subject', PLUGINS_RELEASEMGT_EMAIL_SUBJECT_DEFAULT ); ?>" />
        </td>
      </tr>

      <!-- Email template -->
      <tr>
        <td class="category" width="30%">
          <span class="required">*</span><?php echo plugin_lang_get( 'email_template' ); ?>
        </td>
        <td width="70%">
          <select name="email_template">

<?php

$t_selected = plugin_config_get( 'email_template', PLUGINS_RELEASEMGT_EMAIL_TEMPLATE_DEFAULT );

$t_dir = config_get_global('plugin_path' ). plugin_get_current() . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
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
        </td>
      </tr>

    </table>
          <input tabindex="4" type="submit" class="button" value="<?php echo lang_get( 'submit_button' ) ?>" />
          <?php if ( $t_project_id != ALL_PROJECTS ) { ?><input type="button" class="button" value="<?php echo lang_get( 'revert_to_all_project' ) ?>" onclick="document.forms.plugins_releasemgt.action.value='delete';document.forms.plugins_releasemgt.submit();" /><?php } ?>
  </form>

</div>
</div>



<?php
    layout_page_end();