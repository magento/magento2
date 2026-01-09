/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'mage/translate'
], function ($, $t) {
    'use strict';

    return function (config) {
        var elementId = config.elementId,
            searchUrl = config.searchUrl,
            $container = $('#' + elementId + '_container'),
            $label = $('#' + elementId + '_label'),
            $changeBtn = $('#' + elementId + '_change'),
            $panel = $('#' + elementId + '_panel'),
            $searchInput = $('#' + elementId + '_search'),
            $results = $('#' + elementId + '_results'),
            $hiddenInput = $('#' + elementId),
            searchTimeout = null,
            isOpen = false;

        /**
         * Toggle search panel visibility
         */
        function togglePanel() {
            if (isOpen) {
                closePanel();
            } else {
                openPanel();
            }
        }

        /**
         * Open search panel
         */
        function openPanel() {
            $panel.show();
            $searchInput.val('').focus();
            isOpen = true;
            loadResults('');
        }

        /**
         * Close search panel
         */
        function closePanel() {
            $panel.hide();
            $searchInput.val('');
            $results.empty();
            isOpen = false;
        }

        /**
         * Load search results
         * @param {String} term - Search term
         */
        function loadResults(term) {
            $results.html('<div class="loading">' + $t('Loading...') + '</div>');

            $.ajax({
                url: searchUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    label_part: term,
                    isAjax: true
                },
                success: function (data) {
                    renderResults(data);
                },
                error: function () {
                    $results.html('<div class="no-results">' + $t('Error loading results') + '</div>');
                }
            });
        }

        /**
         * Render search results
         * @param {Array} items - Result items
         */
        function renderResults(items) {
            $results.empty();

            if (!items || items.length === 0) {
                $results.html('<div class="no-results">' + $t('No pages found') + '</div>');
                return;
            }

            $.each(items, function (index, item) {
                var $item = $('<div class="result-item"></div>')
                    .text(item.label)
                    .data('id', item.id)
                    .data('label', item.label)
                    .on('click', function () {
                        selectItem($(this).data('id'), $(this).data('label'));
                    });
                $results.append($item);
            });
        }

        /**
         * Select an item
         * @param {String} id - Item ID (page identifier)
         * @param {String} label - Item label
         */
        function selectItem(id, label) {
            $hiddenInput.val(id);
            $label.text(label);
            closePanel();
        }

        /**
         * Handle search input
         */
        function handleSearch() {
            var term = $searchInput.val();

            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }

            searchTimeout = setTimeout(function () {
                loadResults(term);
            }, 300);
        }

        // Event bindings
        $changeBtn.on('click', function (e) {
            e.preventDefault();
            togglePanel();
        });

        $searchInput.on('keyup', handleSearch);

        // Close panel when clicking outside
        $(document).on('click', function (e) {
            if (isOpen && !$(e.target).closest($container).length) {
                closePanel();
            }
        });

        // Handle escape key
        $searchInput.on('keydown', function (e) {
            if (e.keyCode === 27) { // Escape
                closePanel();
            }
        });
    };
});
