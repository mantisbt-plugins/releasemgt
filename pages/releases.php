<?php
/**
 * ReleaseMgt plugin
 *
 * Original author Vincent DEBOUT
 * modified for new Mantis plugin system by Jiri Hron
 *
 * Created: 2008-01-05
 * Last update: 2012-05-23
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 * @author Jiri Hron <jirka.hron@gmail.com>
 */


require_once( 'core.php' );
require_once( 'bug_api.php' );
html_page_top( plugin_lang_get( 'display_page_title' ) );

$t_user_id = auth_get_current_user_id();
$t_project_id = helper_get_current_project();

$t_releases = version_get_all_rows( $t_project_id, 1 );
$t_project_name = project_get_name( $t_project_id );

$t_user_has_upload_level = user_get_access_level( $t_user_id, $t_project_id ) >= plugin_config_get( 'upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT );

echo '<br /><span class="pagetitle">', string_display( $t_project_name ), ' - ', plugin_lang_get( 'display_page_title' ), '</span><br /><br />';

foreach( $t_releases as $t_release ) {
    $t_prj_id = $t_release['project_id'];
    $t_project_name = project_get_field( $t_prj_id, 'name' );
    $t_release_title = string_display( $t_project_name ) . ' - ' . string_display( $t_release['version'] );
    echo '<tt>' . $t_release_title . '<br />';
    echo str_pad( '', strlen( $t_release_title ), '=' ), '</tt><br /><br />';
    $t_query = 'SELECT id, title, description FROM ' . plugin_table('file') . ' WHERE project_id=' . db_prepare_int( $t_prj_id ) . ' AND version_id=' . db_prepare_int( $t_release['id'] ) . ' ORDER BY title ASC';
    $t_result = db_query( $t_query );
    while( $t_row = db_fetch_array( $t_result ) ) {
        echo '- <a href="' . plugin_page( 'download' ) . '&id=' . $t_row['id'] . '" title="' . plugin_lang_get( 'download_link' ) . '">' . $t_row['title'] . '</a>';
        if ( $t_user_has_upload_level ) {
            echo ' ';
            echo '- [ <a href="' . plugin_page( 'delete' ) . '&id=' . $t_row['id'] . '" onclick="return confirm(\'Are you sure?\');" title=" ' . lang_get( 'delete_link' ) . '">' . lang_get( 'delete_link' ) . '</a> ]';
        }
        if ( $t_row['description'] != '' ) {
            echo '<br /><div style="margin-left: 10px;">' . string_display_links( $t_row['description'] ) . '</div>';
        } else {
            echo '<br />';
        }
        echo '<br />' . "\n";
    }
}

if ( $t_user_has_upload_level && $t_project_id != ALL_PROJECTS ) {
    $t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
    echo '<br /><hr />' . "\n";
    echo '<br /><span class="pagetitle">', plugin_lang_get( 'upload_title' ), '</span><br /><br />';
?>

<form action="<?php echo plugin_page( 'upload' ); ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="plugin" value="releasemgt" />
  <input type="hidden" name="display" value="upload" />
  <input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
  <table class="width100" cellspacing="1">
    <tr class="row-1">
      <td class="category" width="15%">
        <span class="required">*</span><?php echo plugin_lang_get( 'file_count' ) ?>
      </td>
      <td width="85%">
	<input name="file_count" id="file_count" type="text" size="3" maxlength="1" value="<?php echo plugin_config_get( 'file_number', PLUGINS_RELEASEMGT_FILE_NUMBER_DEFAULT ); ?>" onchange="javascript:UpdateFileField();" />
      </td>
    </tr>
    <tr class="row-2">
      <td class="category" width="15%">
        <span class="required">*</span><?php echo lang_get( 'select_file' ) ?><br />
        <?php echo '<span class="small">(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)</span>'?>
      </td>
      <td width="85%">
        <div id="FileField"></div>
      </td>
    </tr>
    <tr class="row-1">
      <td class="category" width="15%">
        <?php echo lang_get( 'product_version' ) ?>
      </td>
      <td width="85%">
        <select name="release">
          <?php foreach( $t_releases as $t_release ) {
              echo '<option value="' . $t_release['id'] . '">' . $t_release['version'] . '</option>';
          } ?>
        </select>
      </td>
    </tr>
    <tr class="row-2">
      <td class="category" width="15%">
        <?php echo lang_get( 'description' ) ?>
      </td>
      <td width="85%">
        <div id="DescriptionField">
        </div>
      </td>
    </tr>
    <tr>
      <td class="left">
        <span class="required"> * <?php echo lang_get( 'required' ) ?></span>
      </td>
      <td class="center">
        <input type="submit" class="button" value="<?php echo lang_get( 'upload_file_button' ) ?>" />
      </td>
    </tr>
  </table>
  <script type="text/javascript" language="javascript">
    <!--

      function UpdateFileField() {
          var file_count = document.getElementById( 'file_count').value;
          var inner = '';
          var innerDescription = '';

          for( var i=0; i<file_count; i++ ) {
              if ( inner != '' ) {
                  inner += '<br />';
              }
              inner += '<input name="file_' + i + '" type="file" size="40" />';
              innerDescription += '<textarea name="description_' + i + '" cols="80" rows="10" wrap="virtual"></textarea><br/>'
          }
          document.getElementById( 'FileField' ).innerHTML = inner;
          document.getElementById( 'DescriptionField' ).innerHTML = innerDescription;
      }

    UpdateFileField();

    -->
  </script>
</form>
<?php

}

?>

<?php
    html_page_bottom();
?>