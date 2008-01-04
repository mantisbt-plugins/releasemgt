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

$t_user_id = auth_get_current_user_id();
$t_project_id = helper_get_current_project();

html_page_top1( lang_get( 'plugins_releasemgt_display_page_title' ) );
html_page_top2();

$t_releases = version_get_all_rows( $t_project_id, 1 );
$t_project_name = project_get_name( $t_project_id );

$t_user_has_upload_level = user_get_access_level( $t_user_id, $t_project_id ) >= config_get( 'plugins_releasemgt_upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT );

echo '<br /><span class="pagetitle">', string_display( $t_project_name ), ' - ', lang_get( 'plugins_releasemgt_display_page_title' ), '</span><br /><br />';

foreach( $t_releases as $t_release ) {
    $t_prj_id = $t_release['project_id'];
    $t_project_name = project_get_field( $t_prj_id, 'name' );
    $t_release_title = string_display( $t_project_name ) . ' - ' . string_display( $t_release['version'] );
    echo '<tt>' . $t_release_title . '<br />';
    echo str_pad( '', strlen( $t_release_title ), '=' ), '</tt><br /><br />';
    $t_query = 'SELECT id, title, description FROM ' . config_get( 'db_table_prefix' ) . '_plugins_releasemgt_file' . config_get( 'db_table_suffix' ) . ' WHERE project_id=' . db_prepare_int( $t_prj_id ) . ' AND version_id=' . db_prepare_int( $t_release['id'] ) . ' ORDER BY title ASC';
    $t_result = db_query( $t_query );
    while( $t_row = db_fetch_array( $t_result ) ) {
        echo '- <a href="plugins_page.php?plugin=releasemgt&display=download&id=' . $t_row['id'] . '" title="' . lang_get( 'plugins_releasemgt_download_link' ) . '">' . $t_row['title'] . '</a>';
        if ( $t_user_has_upload_level ) {
            echo ' ';
            print_bracket_link( 'plugins_page.php?plugin=releasemgt&display=delete&id=' . $t_row['id'], lang_get( 'delete_link' ) );
        }
        if ( $t_row['description'] != '' ) {
            echo '<br /><div style="margin-left: 10px;">' . string_display_links( $t_row['description'] ) . '</div>';
        }
        echo '<br />' . "\n";
    }
}

if ( $t_user_has_upload_level && $t_project_id != ALL_PROJECTS ) {
    $t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
    echo '<br /><hr />' . "\n";
    echo '<br /><span class="pagetitle">', lang_get( 'plugins_releasemgt_upload_title' ), '</span><br /><br />';
?>
<form action="plugins_page.php" method="post" enctype="multipart/form-data">
  <input type="hidden" name="plugin" value="releasemgt" />
  <input type="hidden" name="display" value="upload" />
  <input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
  <table class="width100" cellspacing="1">
    <tr class="row-1">
      <td class="category" width="15%">
        <span class="required">*</span><?php echo lang_get( 'select_file' ) ?><br />
        <?php echo '<span class="small">(' . lang_get( 'max_file_size' ) . ': ' . number_format( $t_max_file_size/1000 ) . 'k)</span>'?>
      </td>
      <td width="85%">
	<input name="file" type="file" size="40" />
      </td>
    </tr>
    <tr class="row-2">
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
    <tr class="row-1">
      <td class="category" width="15%">
        <?php echo lang_get( 'description' ) ?>
      </td>
      <td width="85%">
	<textarea name="description" cols="80" rows="10" wrap="virtual"></textarea>
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
</form>
<?php 

}

html_page_bottom1( __FILE__ );

?>
