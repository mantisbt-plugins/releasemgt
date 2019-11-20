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

function size_display($bytes)
{
	$unit = intval(log($bytes, 1024));
	$units = array('B', 'KB', 'MB', 'GB');

	if (array_key_exists($unit, $units) === true)
		return sprintf('%4.1f %s', $bytes / pow(1024, $unit), $units[$unit]);

    return $bytes;
}

require_once( 'core.php' );
require_once( 'bug_api.php' );
require_once( 'constant_api.php' );
require_once( 'releasemgt_api.php' );

layout_page_header( plugin_lang_get( 'display_page_title' ) );
layout_page_begin( plugin_page('releases') );

$t_user_id = auth_get_current_user_id();
$t_project_id = helper_get_current_project();

$t_releases = version_get_all_rows( $t_project_id, 1 );
$t_common = array( 'id' => PLUGINS_RELEASEMGT_NO_RELEASE_VERSION, 'project_id' => $t_project_id, 
    'version' => plugin_lang_get( 'no_release_version_name' ), 
    'description' => plugin_lang_get( 'no_release_version_description' ), 
    'released' => 1, 'obsolete' => 0,'date_order' => 0x7FFFFFFF);
array_push( $t_releases, $t_common );

$t_project_name = project_get_name( $t_project_id );

$t_user_has_upload_level = user_get_access_level( $t_user_id, $t_project_id ) >= plugin_config_get( 'upload_threshold_level', PLUGINS_RELEASEMGT_UPLOAD_THRESHOLD_LEVEL_DEFAULT );

releasemgt_plugin_page_title( string_display_line( $t_project_name ), plugin_lang_get( 'display_page_title' ) );

echo '<div hidden title="' . plugin_lang_get( 'confirm_delete_file' ) . '" id="releasemgt_confirm_delete_file"></div>';
echo '<div hidden title="' . plugin_lang_get( 'confirm_delete_version' ) . '" id="releasemgt_confirm_delete_version"></div>';
foreach( $t_releases as $t_release ) {
    $t_prj_id = $t_release['project_id'];
    $t_release_title = string_display( $t_project_name ) . ' - ' . string_display( $t_release['version'] );
    
    $t_query = 'SELECT id, title, filesize, description, enabled, release_type
		FROM ' . plugin_table('file') . '
		WHERE project_id=' . db_param() . ' AND version_id=' . db_param();
    if( !$t_user_has_upload_level )
    {
	$t_query .= ' AND enabled>0';
    }
    $t_query .= ' ORDER BY title ASC';
    $t_result = db_query( $t_query, array( (int)$t_prj_id, (int)$t_release['id'] ) );
    
    if( db_num_rows( $t_result ) == 0 )
        continue;
    
    releasemgt_plugin_release_title( $t_release_title, $t_release['version'] );
        
    echo '<ul style="padding-left: 12px;">';
    while( $t_row = db_fetch_array( $t_result ) ) {
	$t_item_prefix = $t_row['description'];
	$t_item_postfix = '';
	$t_item_text = $t_row['title'];
	if( $t_item_prefix != '' )
	{
	    $t_item_prefix .= ' (';
	    $t_item_postfix = ')';
	}
	$t_file_class = 'releasemgt-enabled-file';
        if( $t_user_has_upload_level && $t_row['enabled']==0 ){
	    $t_file_class = 'releasemgt-disabled-file';
        }
	
        // Attention! Do not use plugin_page() for download link
        // It causes security header to be submitted prior plugin 
        // has a chance to disable default header submission. 
        // As result "download" header can't be submitted properly
        // So, we are using releasemgt_plugin_page_url() instead
        echo '<li style="padding-bottom: 6px;" class="' . $t_file_class . '">' . $t_item_prefix 
    	    . '<a class="' . $t_file_class . '"  href="' . releasemgt_plugin_page_url( 'download' ) . '?id=' . $t_row['id'] . '" title="' . plugin_lang_get( 'download_link' ) . '">'
	    . $t_row['title'] . '</a>' . $t_item_postfix;
		echo ' - ' . size_display($t_row['filesize']);
        if ( $t_user_has_upload_level ) {
            $t_enable_text =  plugin_lang_get( $t_row['enabled'] ? 'disable_link' : 'enable_link' );
            echo '&emsp; <a class="btn btn-xs btn-primary btn-white btn-round releasemgt_enable" href="' . plugin_page( 'enable' ) . '&id=' . $t_row['id'] . '&enbl=' . ($t_row['enabled'] ? '0' : '1') . '" title=" ' . $t_enable_text . '">' . $t_enable_text . '</a>';
            echo '&emsp; <a class="btn btn-xs btn-primary btn-white btn-round releasemgt_delete" href="' . plugin_page( 'delete' ) . '&id=' . $t_row['id'] . '" title=" ' . lang_get( 'delete_link' ) . '">' . lang_get( 'delete_link' ) . '</a>';
        }
    }
    echo '</ul>';
    echo "</div>\n";
}

if ( $t_user_has_upload_level && $t_project_id != ALL_PROJECTS ) {
    //$t_max_file_size = (int)min( ini_get_number( 'upload_max_filesize' ), ini_get_number( 'post_max_size' ), config_get( 'max_file_size' ) );
    $t_max_file_size = releasemgt_max_upload_size()[0];
    echo '<br /><hr />' . "\n";
    releasemgt_plugin_section_title( plugin_lang_get('upload_title'), 'fa-upload',  'releasemgt_upload' );
?>

<form action="<?php echo plugin_page( 'upload' ); ?>" method="post" enctype="multipart/form-data">
  <input type="hidden" name="plugin" value="releasemgt" />
  <input type="hidden" name="display" value="upload" />
  <input type="hidden" name="max_file_size" value="<?php echo $t_max_file_size ?>" />
  <table class="width100 table table-striped table-bordered table-condensed" cellspacing="1">
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
        <span class="required">*</span><?php echo plugin_lang_get( 'file_count' ) ?>
      </td>
      <td width="85%">
        <input name="file_count" id="file_count" type="text" size="3" maxlength="1" value="<?php echo plugin_config_get( 'file_number', PLUGINS_RELEASEMGT_FILE_NUMBER_DEFAULT ); ?>" >
      </td>
    </tr>
    <tr class="row-1">
      <td class="category" width="15%">
        <span class="required">*</span><?php echo lang_get( 'select_files' ) ?><br />
        <?php echo '<span class="small">(' . lang_get( 'max_file_size_label' ) . ' ' . number_format( $t_max_file_size/1000 ) . 'kB)</span>'?>
      </td>
      <td width="85%">
<?php
        // Check if plugin properly configured
        $t_cfgOk = true;
        $t_method = plugin_config_get( 'upload_method', PLUGINS_RELEASEMGT_UPLOAD_METHOD_DEFAULT );
        $t_cfg_err;
        switch ( $t_method ) {
          //case FTP:
          case DISK:
            $t_disk_dir = plugin_config_get( 'disk_dir', PLUGINS_RELEASEMGT_DISK_DIR_DEFAULT );
            if( empty($t_disk_dir) ){
                $t_cfgOk = false;
                $t_cfg_err = 'File disk directory is not configured';
            }
            else if( !is_dir( $t_disk_dir ) ){
                $t_cfgOk = false;
                $t_cfg_err = 'File disk directory "' . $t_disk_dir . '" does not exist';
            }
            break;
          case DATABASE:
            break;
        }

        if( $t_cfgOk ){
            echo '<div id="FileField"></div>';
        } else {
            echo '<div style="color:red">';
            echo 'Invalid plugin configuration:<br>';
            echo $t_cfg_err;
            echo '</div>';
        }
?>
      </td>
    </tr>
    <tr>
      <td class="left">
        <span class="required"> * <?php echo lang_get( 'required' ) ?></span>
      </td>
      <td class="center">
      </td>
    </tr>
  </table>
  
<?php
  if( $t_cfgOk ){
?>
  <input type="submit" class="btn btn-primary btn-white btn-round" value="<?php echo lang_get( 'upload_files_button' ) ?>" />
<!--  <input type="submit" class="button" value="<?php echo lang_get( 'upload_files_button' ) ?>" /> -->
  <script src="<?php echo plugin_file( 'releases.js' ) ?>"></script>  
<?php
}
?>
</form>
<?php
    echo '</div>';
    echo '</div>';
}

?>

<?php
    layout_page_end();