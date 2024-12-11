(function($){

    $(document).ready(function(){
        console.log("appointedd admin init");
        //GetServices();
        //sync_ids();

        var $sync = $('.appointedd-sync-button');

        $sync.on('click', function(event){
            event.preventDefault();
            console.log("sync button clicked");
            sync_ids($sync);

        });

        $('.sortable').sortable();
    });
    
    var sync_ids = function( $element ){
        var data = {
            action: "sync_ids",
        }
        //url = url + "availability/slots";

        $.ajax({
            url: appointedd_admin_ajaxobj.ajax_url,
            type: "GET",
            data: data,
            success: function(res){
                var total = res.total;
                var failed = res.failed;
                console.log("total", total, "failed", failed);

                var $result_container = $('<div class="sync-results-container"></div>');
                var $total_p = $('<h5 class="sync-total">Successfully synced data for </h5>');
                $total_p.append(total).append(" celebrants");

                var $failed_p = $('<h5 class="sync-failed">Failed to sync data for the following celebrants;</h5>');
                $.each(failed, function(){
                    $failed_p.append('<h4>' + this + '</h4>');
                });

                $failed_p.append("<h4><strong>Ensure the names match and try again</strong></h4>");

                $result_container.append($total_p).append($failed_p);

                $element.after($result_container);

            },
            error: function(error){
                console.log("Error", error);
            }
        })
    }

})( jQuery );