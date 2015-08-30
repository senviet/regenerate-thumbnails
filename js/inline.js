(
    function($){
        $( document ).ready(function() {
            $(document).on('click', '.regenerate_thumbnail', function(){
                var postId = $(this).data('id');
                requestRegenerate(postId, function(data){
                    alert(data.message);
                })
            });
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