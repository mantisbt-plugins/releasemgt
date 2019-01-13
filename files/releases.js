

function UpdateFileField() {
    var file_count = document.getElementById( 'file_count').value;
    var inner = '';
    //var innerDescription = '';
    
    for( var i=0; i<file_count; i++ ) {
        if ( inner != '' ) {
            inner += '<br /><br/>';
        }
        inner += '<input name="file_' + i + '" type="file" size="40" />';
        inner += '<textarea name="description_' + i + '" cols="80" rows="2" wrap="virtual"></textarea>'
    }
    document.getElementById( 'FileField' ).innerHTML = inner;
    //document.getElementById( 'DescriptionField' ).innerHTML = innerDescription;
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
            
