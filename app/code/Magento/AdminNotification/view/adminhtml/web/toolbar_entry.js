/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    "jquery",
    "jquery/ui"
], function($){
    'use strict';

    $(document).ready(function() {
        // Mark notification as read via AJAX call
        var markNotificationAsRead = function(notificationId) {
            var requestUrl = $('.notifications-summary .dropdown-menu').attr('data-mark-as-read-url');
            $.ajax({
                url: requestUrl,
                type: 'POST',
                dataType: 'json',
                data: {
                    id: notificationId
                },
                showLoader: false
            });
        };
        var notificationCount = $('.notifications-summary').attr('data-notification-count');
        // Remove notification from the list
        var removeNotificationFromList = function(notificationEntry) {
            notificationEntry.remove();
            notificationCount--;
            $('.notifications-summary').attr('data-notification-count', notificationCount);

            if (notificationCount == 0) {
                // Change appearance of the bubble and its behavior when the last notification is removed
                $('.notifications-summary .dropdown-menu').remove();
                var notificationIcon = $('.notifications-summary .notifications-icon');
                notificationIcon.removeAttr('data-toggle');
                notificationIcon.off('click.dropdown');
                $('.notifications-action .counter').text('');
                $('.notifications-action .counter').hide();
            } else {
                $('.notifications-action .counter').text(notificationCount);
                // Modify caption of the 'See All' link
                var actionElement = $('.notifications-summary .dropdown-menu .last .action-more');
                actionElement.text(actionElement.text().replace(/\d+/, notificationCount));
            }
        };

        // Show popup with notification details
        var showNotificationDetails = function(notificationEntry) {
            var popupElement = notificationEntry.find('.notifications-dialog-content').clone();
            var notificationId = notificationEntry.attr('data-notification-id');
            var dialogClassSeverity = 'notifications-entry-dialog';
            if (notificationEntry.attr('data-notification-severity')) {
                dialogClassSeverity = 'notifications-entry-dialog notifications-entry-dialog-critical';
            }
            popupElement.dialog({
                title: popupElement.attr('data-title'),
                minWidth: 500,
                modal: true,
                dialogClass: dialogClassSeverity,
                buttons: [
                    {
                        text: popupElement.attr('data-acknowledge-caption'),
                        'class': 'action-acknowledge primary',
                        click: function(event) {
                            markNotificationAsRead(notificationId);
                            removeNotificationFromList(notificationEntry);
                            $(this).dialog('close');
                        }
                    },
                    {
                        text: popupElement.attr('data-cancel-caption'),
                        'class': 'action-cancel',
                        click: function(event) {
                            $(this).dialog('close');
                        }
                    }
                ]
            });
            popupElement.parent().attr('aria-live','assertive');
            popupElement.dialog('open');
        };

        // Show notification description when corresponding item is clicked
        $('.notifications-summary .dropdown-menu .notifications-entry').on('click.showNotification', function(event) {
            // hide notification dropdown
            $('.notifications-summary .notifications-icon').trigger('click.dropdown');
            showNotificationDetails($(this));
            event.stopPropagation();
        });

        // Remove corresponding notification from the list and mark it as read
        $('.notifications-close').on('click.removeNotification', function(event) {
            var notificationEntry = $(this).closest('.notifications-entry')
            var notificationId = notificationEntry.attr('data-notification-id');
            markNotificationAsRead(notificationId);
            removeNotificationFromList(notificationEntry);
            event.stopPropagation();
        });

        // Hide notifications bubble
        if (notificationCount == 0) {
            $('.notifications-action .counter').hide();
        } else {
            $('.notifications-action .counter').show();
        }
    });

});