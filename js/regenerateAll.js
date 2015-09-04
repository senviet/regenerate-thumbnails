(
    function($){
        $( document ).ready(function() {
            var isProcessing = false;
            var regenerateStatus = $('#regenerateStatus');
            var statusString = $('#statusString');
            var processedCountText = $('#processed');
            var totalCountText = $('#total');
            var startStopRegenerateButton = $('#startStopRegenerate');
            $(document).on('click', '#startStopRegenerate', function(e){
                e.preventDefault();
                if(!isProcessing){
                    isProcessing = true;
                    regenerateStatus.show();
                    startStopRegenerateButton.text('Dừng lại');
                    requestNextStep();
                }
                else
                {
                    isProcessing = false;
                    startStopRegenerateButton.text('Bắt đầu');
                }
            });
            function requestNextStep(){
                $.ajax({
                    method: "POST",
                    url: ajaxurl ,
                    data: {
                        action:'regeneratethumbnail-next-step'
                    }
                }).success(function( data ) {
                    console.log(data);
                    if(data.success){
                        processedCountText.text(data.proccessed);
                        totalCountText.text(data.total);
                        if(typeof  data.done == 'undefined'){
                            requestNextStep();
                        }
                        else{
                            startStopRegenerateButton.click();
                            alert('Done');
                        }
                    }
                });
            }
        });
    }
)(jQuery);