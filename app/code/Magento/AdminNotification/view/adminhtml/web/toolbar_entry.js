/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'jquery',
    'jquery/ui',
    'domReady!'
], function ($) {
    'use strict';

    /**
     * Mark notification as read via AJAX call.
     *
     * @param {String} notificationId
     */
    var markNotificationAsRead = function (notificationId) {
            var requestUrl = $('.notifications-wrapper .admin__action-dropdown-menu').attr('data-mark-as-read-url');

            $.ajax({
                url: requestUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: notificationId
                },
                showLoader: false
            });
        },
        notificationCount = $('.notifications-wrapper').attr('data-notification-count'),

        /**
         * Remove notification from the list.
         *
         * @param {jQuery} notificationEntry
         */
        removeNotificationFromList = function (notificationEntry) {
            var notificationIcon, actionElement;

            notificationEntry.remove();
            notificationCount--;
            $('.notifications-wrapper').attr('data-notification-count', notificationCount);

            if (notificationCount == 0) {// eslint-disable-line eqeqeq
                // Change appearance of the bubble and its behavior when the last notification is removed
                $('.notifications-wrapper .admin__action-dropdown-menu').remove();
                notificationIcon = $('.notifications-wrapper .notifications-icon');
                notificationIcon.removeAttr('data-toggle');
                notificationIcon.off('click.dropdown');
                $('.notifications-action .notifications-counter').text('').hide();
            } else {
                // Change top counter only for allowable range
                if (notificationCount <= 99) {
                    $('.notifications-action .notifications-counter').text(notificationCount);
                }
                $('.notifications-entry-last .notifications-counter').text(notificationCount);
                // Modify caption of the 'See All' link
                actionElement = $('.notifications-wrapper .admin__action-dropdown-menu .last .action-more');
                actionElement.text(actionElement.text().replace(/\d+/, notificationCount));
            }
        },

        /**
         * Show notification details.
         *
         * @param {jQuery} notificationEntry
         */
        showNotificationDetails = function (notificationEntry) {
            var notificationDescription = notificationEntry.find('.notifications-entry-description'),
                notificationDescriptionEnd = notificationEntry.find('.notifications-entry-description-end');

            if (notificationDescriptionEnd.length > 0) {
                notificationDescriptionEnd.addClass('_show');
            }

            if (notificationDescription.hasClass('_cutted')) {
                notificationDescription.removeClass('_cutted');
            }
        };

    // Show notification description when corresponding item is clicked
    $('.notifications-wrapper .admin__action-dropdown-menu .notifications-entry').on(
        'click.showNotification',
        function (event) {
            // hide notification dropdown
            $('.notifications-wrapper .notifications-icon').trigger('click.dropdown');

            showNotificationDetails($(this));
            event.stopPropagation();
        }
    );

    // Remove corresponding notification from the list and mark it as read
    $('.notifications-close').on('click.removeNotification', function (event) {
        var notificationEntry = $(this).closest('.notifications-entry'),
            notificationId = notificationEntry.attr('data-notification-id');

        markNotificationAsRead(notificationId);
        removeNotificationFromList(notificationEntry);

        // Checking for last unread notification to hide dropdown
        if (notificationCount == 0) {// eslint-disable-line eqeqeq
            $('.notifications-wrapper').removeClass('active')
                .find('.notifications-action')
                .removeAttr('data-toggle')
                .off('click.dropdown');
        }
        event.stopPropagation();
    });

    // Hide notifications bubble
    if (notificationCount == 0) {// eslint-disable-line eqeqeq
        $('.notifications-action .notifications-counter').hide();
    } else {
        $('.notifications-action .notifications-counter').show();
    }
});
