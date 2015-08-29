(
    function($){
        $( document ).ready(function() {
            $(document).on('click', '.regenerate_thumbnail', function(){
                var postId = $(this).data('id');
                $.ajax({
                    method: "POST",
                    url: ajaxurl ,
                    data: {
                        action:'regeneratethumbnail',
                        attachmentId: postId
                    }
                 }).success(function( data ) {
                        alert(data.message );
                });
            });
        });
    }
)(jQuery);