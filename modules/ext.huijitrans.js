$messageTools = translateEditor.createMessageTools(),
$messageKeyLabel = $( '<div>' )
    .addClass( 'ten columns messagekey' )
    .text( this.message.title )
    .append(
    $( '<span>' ).addClass( 'caret' ),
    $messageTools
)
    .on( 'click', function ( e ) {
        $messageTools.toggleClass( 'hide' );
        e.stopPropagation();
    } );