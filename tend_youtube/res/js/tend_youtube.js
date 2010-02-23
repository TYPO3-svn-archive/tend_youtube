/* $Id$ */

jQuery.noConflict();

(function($) {


    /* log to firebug */
    function log(e){
        if( typeof(console) != "undefined" ) console.log(e);
    }

    /* ajax call to eID */
    function uploadToYouTube(){
        $("#upload_state").fadeIn("slow");

        return void(0);

        $.getJSON('index.php?eID=tend_youtube', function(data) {
            $("#upload_state").fadeOut("slow",function(){
                window.location.href = unescape(window.location.href);
            });
        });

    }
    
})(jQuery);