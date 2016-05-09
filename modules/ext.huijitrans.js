$(function(){
    $('#wiki-body').on('click','.tux-message-item-compact',function(){
        $(this).find('.tux-proofread-edit').trigger('click');
        $(this).parents('.tux-message-proofread').siblings().removeClass('open');
    });
    $('#wiki-body').on('click','.source-message-content',function(){
        var content = $(this).text();
        $(this).parents('.tux-message-editor').find('.tux-textarea-translation').val(content);
    })
})