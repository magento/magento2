define(['jquery',], function($){
    $(document).ready(function() {
        $.get( "http://localhost/git/magento2/rest/V1/helloworld", function( data ) {
            $( ".display-message-from-api" ).html( data );
        });
    });
});
