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

?>
Hello,<br />
<br />
<?php if ( $t_template['files_count'] == 1 ) { ?>A new file has been uploaded.<?php } else { ?>New files have been uploaded.<?php } ?><br />
<br />
<ul><?php for( $i=0; $i<$t_template['files_count']; $i++ ) { ?><li> <b><?php echo $t_template['files'][$i]['file_name']; ?></b><br />
<?php echo $t_template['files'][$i]['file_html_description']; ?><br />
<br />
You can download it at the following address: <a href="<?php echo $t_template['files'][$i]['file_url']; ?>" title="Download">Click here</a><br />
</li><?php } ?></ul>
