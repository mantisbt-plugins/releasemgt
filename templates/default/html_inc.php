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

?>
Hello,<br />
<br />
<?php if ( $t_template['files_count'] == 1 ) { ?>A new file has been uploaded.<?php } else { ?>New files have been uploaded.<?php } ?><br />
<br />
<ul><?php for( $i=0; $i<$t_template['files_count']; $i++ ) { ?><li> <b><?php echo $t_template['files'][$i]['file_name']; ?></b><br />
<?php echo $t_template['files'][$i]['file_html_description']; ?><br />
<br />
You can donwload it at the following address: <a href="<?php echo $t_template['files'][$i]['file_url']; ?>" title="Download">Click here</a><br />
</li><?php } ?></ul>
