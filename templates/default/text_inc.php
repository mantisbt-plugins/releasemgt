<?php

/**
 * ReleaseMgt plugin
 *
 *
 * Created: 2008-01-05
 * Last update: 2008-11-08
 *
 * @link http://deboutv.free.fr/mantis/
 * @copyright 
 * @author Vincent DEBOUT <vincent.debout@morinie.fr>
 */

?>
Hello,

<?php if ( $t_template['files_count'] == 1 ) { ?>A new file has been uploaded.<?php } else { ?>New files have been uploaded.<?php } ?>

<?php for( $i=0; $i<$t_template['files_count']; $i++ ) { ?>- <?php echo $t_template['files'][$i]['file_name']; ?>

<?php echo $t_template['files'][$i]['file_description']; ?>


You can donwload it at the following address:

<<?php echo $t_template['files'][$i]['file_url']; ?>>
<?php } ?>