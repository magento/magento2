define([
    "jquery"
], function($){

    return function (params, elem) {

        elem.on('click', function() {
            if ($.validator.validateElement($('[name="current_password"]'))) {
                postData = {'data' : {
                    'user_id': params.objId,
                    'current_password': $('[name="current_password"]').val()
                }}
                deleteConfirm(params.message, params.url, params.postData);
            }
        });
    }
});
