/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'domReady!'
], function ($) {
    'use strict';

    /* Form with auto submit feature */
    $('form[data-auto-submit="true"]').submit();

    /**
     * Sets proper form key for every form that gets submitted on the page.
     */
    $(document).on('submit', 'form', function (e) {
        var baseUrl = window.BASE_URL,
            $form = $(e.target),
            $formKeyElement,
            formKey = $('input[name="form_key"]').val(),
            formMethod = $form.prop('method'),
            formAction = $form.prop('action'),
            isActionExternal = formAction.indexOf(baseUrl) !== 0;

        /**
         * Take action only for internal forms that are not using GET method.
         */
        if (isActionExternal || formMethod === 'get') {
            return;
        }

        /**
         * Verifies that existing auto-added form key is a direct form child element.
         */
        $formKeyElement = $form.children('input[name="form_key"]');

        if (!$formKeyElement.length) {
            $formKeyElement = $('<input>').prop({
                type: 'hidden',
                name: 'form_key'
            });
            $form.append($formKeyElement);
        }

        $formKeyElement.val(formKey);
    });
});
