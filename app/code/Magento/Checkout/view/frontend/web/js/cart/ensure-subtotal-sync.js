/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';

    /**
     * Compare central cart subtotal with summary subtotal and click Update once if mismatched.
     */
    return function initEnsureSubtotalSync(config, element)
    {
        const $root = $(element || document);

        // Check if already synced on the body element (persists across form replacements)
        if ($('body').data('cart-synced')) {
            return;
        }

        function parsePrice(text)
        {
            if (!text) {
                return NaN;
            }

            // Remove non-numeric except . , - then normalize
            let cleaned = ('' + text).replace(/[^0-9,.-]/g, '');

            // If both , and . exist, assume , is thousands
            if (cleaned.indexOf(',') > -1 && cleaned.indexOf('.') > -1) {
                cleaned = cleaned.replace(/,/g, '');
            } else if (cleaned.indexOf(',') > -1 && cleaned.indexOf('.') === -1) {
                // European format: swap , to .
                cleaned = cleaned.replace(/,/g, '.');
            }

            const n = parseFloat(cleaned);

            return isNaN(n) ? NaN : Math.round(n * 100) / 100;
        }

        function getCentralSubtotal()
        {
            // Sum of row totals on the table
            let sum = 0;

            $root.find('#shopping-cart-table .col.subtotal .price-excluding-tax .cart-price').each(function () {
                const text = $(this).text(), val = parsePrice(text);

                if (!isNaN(val)) {
                    sum += val;
                }
            });

            return Math.round(sum * 100) / 100;
        }

        function getSummarySubtotal()
        {
            // Summary subtotal in cart totals knockout template
            const text = $('#cart-totals .totals.sub .amount .price').first().text();

            return parsePrice(text);
        }

        function trySync()
        {
            // Skip when cart uses pagination; visible rows don't represent full subtotal
            if ($root.find('.cart-products-toolbar').length) {
                return;
            }

            const central = getCentralSubtotal(), summary = getSummarySubtotal();

            if (!isNaN(central) && !isNaN(summary) && central !== summary) {
                // Mark as synced immediately to prevent multiple calls
                $('body').data('cart-synced', true);

                // Reload cart content via AJAX
                $('body').trigger('processStart');
                $.ajax({
                    url: window.location.href,
                    type: 'GET',
                    data: { ajax: 1 },
                    success: function (response) {
                        // Extract and replace cart form
                        const newContent = $(response).find('#form-validate');

                        // Replace the form with the new content
                        $('#form-validate').replaceWith(newContent);
                        // Reinitialize widgets on new content
                        $('#form-validate').trigger('contentUpdated');
                    },
                    error: function () {
                        $('body').trigger('processStop');
                        $('body').data('cart-synced', false);
                    },
                    complete: function () {
                        $('body').trigger('processStop');
                    }
                });
            }
        }

        // Initial attempt after DOM ready and after a short delay to allow KO to render totals
        $(function () {
            trySync();
            setTimeout(trySync, 300);

            // Observe changes in totals area to re-check once
            const totals = document.getElementById('cart-totals');

            if (totals && typeof MutationObserver !== 'undefined') {
                const obs = new MutationObserver(function () {
                    trySync();

                    if ($('body').data('cart-synced')) {
                        obs.disconnect();
                    }
                });

                obs.observe(totals, {
                    childList: true,
                    subtree: true,
                    characterData: true
                });
            }
        });
    };
});
