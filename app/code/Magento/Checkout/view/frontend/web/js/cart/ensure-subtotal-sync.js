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
        let clicked = false;

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

            $root.find('#shopping-cart-table .col.subtotal .cart-price').each(function () {
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

            if (clicked) {
                return;
            }

            const central = getCentralSubtotal(), summary = getSummarySubtotal();

            if (!isNaN(central) && !isNaN(summary) && central !== summary) {
                const $updateBtn = $root.find('.cart.main.actions button.action.update');

                if ($updateBtn.length) {
                    clicked = true;
                    $updateBtn.trigger('click');
                }
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

                    if (clicked) {
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
