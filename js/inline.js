(
    function($){
        $( document ).ready(function() {
            /**
             * Single regenerate action link
             */
            $(document).on('click', '.regenerate_thumbnail', function(e){
                var postId = $(this).data('id');
                var curentEl = $(this);
                var parentEl = curentEl.closest('.row-actions');
                e.preventDefault();
                curentEl.text('Regenerating ...');
                parentEl.css('visibility', 'visible');
                requestRegenerate(postId, function(data){
                    curentEl.text(data.message);
                    setTimeout(function(){
                        parentEl.css('visibility', '');
                    },3000);
                });
            });
/**
 * For bulk regenerate
 * @type {*|HTMLElement}
 */
var topSelector = $('#bulk-action-selector-top');
var bottomSelector = $('#bulk-action-selector-bottom');
topSelector.append('<option value="regenerate">Regenerate thumbnail</option>');
bottomSelector.append('<option value="regenerate">Regenerate thumbnail</option>');
$(document).on('click', '#doaction', onActionSubmit);
$(document).on('click', '#doaction2', onActionSubmit);
function onActionSubmit(e) {
    if( (topSelector.val() == 'regenerate') || (bottomSelector.val() =='regenerate')){
        e.preventDefault();
        $("input[name^='media']:checked:enabled",'#posts-filter').each(function(){
            var attachmentId = $(this).val();
            var nameSpan = $('#post-'+attachmentId+' > td.title.column-title.has-row-actions.column-primary > strong > a > span:nth-child(2)');
            var statusEl = nameSpan.find('.status');
            if(statusEl.length == 0) {
                statusEl = $("<i style='margin-left: 10px;color:#636363' class='status'>Regenerating...</i>");
                nameSpan.append(statusEl);
            }
            else{
                statusEl.css('color', '#636363');
                statusEl.text('Regenerating...');
            }
            requestRegenerate(attachmentId, function(data){
                if(data.success){
                    statusEl.css('color','#2A8A04');
                }
                else{
                    statusEl.css('color','#BB0D0D');
                }
                statusEl.text(data.message);
            })
        });
    }
}

            /**
             * make a regenerate request
             * @param id
             * @param successCallback
             */
            function requestRegenerate(id, successCallback){
                $.ajax({
                    method: "POST",
                    url: ajaxurl ,
                    data: {
                        action:'regeneratethumbnail',
                        attachmentId: id
                    }
                }).success(function( data ) {
                    if(successCallback){
                        successCallback.call(this, data);
                    }
                });
            }
        });
    }
)(jQuery);