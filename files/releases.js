

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

UpdateFileField();

document.addEventListener('DOMContentLoaded', function () {
  document.getElementById('file_count')
            .addEventListener('change', UpdateFileField )
  });
            
