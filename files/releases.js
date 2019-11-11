

function UpdateFileField() {
    var file_count = document.getElementById( 'file_count').value;
    var inner = '<table width="100%">';
    
    for( var i=0; i<file_count; i++ ) {
        var cls = i % 2 == 0 ? 'releasemgt-odd-row' : '';
        if ( i > 0  ) {
            inner += '<tr class="'+cls+'"><td colspan="2">&nbsp;</td></tr>';
        }
        inner += '<tr class="'+cls+'"><td class="releasemgt-file-num">#'+(i+1)+'&nbsp;</td><td> <input name="file_' + i + '" type="file" size="40" /> </td></tr>';
        inner += '<tr class="'+cls+'"><td>&nbsp;</td><td> <textarea name="description_' + i + '" cols="80" rows="2" wrap="virtual"></textarea></td></tr>';
    }
    inner += '</table>';
    document.getElementById( 'FileField' ).innerHTML = inner;
}

function ConfirmDelete(event)
{
    mssg = document.getElementById('releasemgt_confirm_delete_file').title;
    if( confirm( mssg ) )
    {
	return true;
    }
    
    event.preventDefault();
    return false;
}

UpdateFileField();

document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('file_count')
            .addEventListener('change', UpdateFileField );

/*            
  document.getElementsByClassName('releasemgt_delete').forEach(
    function(elem){
	elem.addEventListener('onclick', ConfirmDelete );
    }
  );
*/
  var elems = document.getElementsByClassName('releasemgt_delete');
  //alert( elems.length );
  for( var i=0; i < elems.length; i++ )
  {
    elems[i].addEventListener('click', ConfirmDelete);
    //alert(i);
  }
  
});
            
