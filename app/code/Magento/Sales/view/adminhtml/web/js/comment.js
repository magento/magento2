/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
define([
    'jquery',
    'mage/translate'
// eslint-disable-next-line strict
], function ($) {
    'use strict';

    /**
     * Open edit comment modal
     *
     * @param {Event} event
     */
    function openModal(event) {
        var element = $(event.target),
            noteListContainer = element.parent('.note-list-customer'),
            editCommentContainer = noteListContainer.siblings('.edit-comment-container'),
            noteListCommentContainer = noteListContainer.siblings('.note-list-comment'),
            commentText = noteListCommentContainer.text(),
            editCommentTextarea = editCommentContainer.find('.edit-comment-textarea');

        $('.edit-comment-container').css('display', 'none');
        $('.note-list-comment').css('display', 'block');
        $('.edit-comment-textarea').attr('disabled', 'disabled');

        noteListCommentContainer.css('display', 'none');
        editCommentContainer.css('display', 'block');

        editCommentTextarea.removeAttr('disabled');
        editCommentTextarea.val(commentText.trim());
    }

    /**
     * Close edit comment modal
     */
    function closeCommentArea() {
        $('.edit-comment-container').css('display', 'none');
        $('.note-list-comment').css('display', 'block');
    }

    /**
     * Update sales entity comment
     *
     * @param {String} url
     * @param {Event} event
     */
    function updateComment(url, event) {
        var element = $(event.target),
            data = {};

        data['comment'] = {
            'form_key': window.FORM_KEY,
            'comment_id': element.attr('data-comment-id'),
            'comment': element.parent().parent('.edit-comment-container').find('.edit-comment-textarea').val()
        };

        $('body').trigger('processStart');

        $.ajax({
            url: url,
            data: data,
            success: function (response) {
                if (response.error) {
                    // eslint-disable-next-line no-alert
                    alert(response.message);
                } else {
                    $('#comments_block').parent().html(response);
                }
            },

            /**
             * Complete callback.
             */
            complete: function () {
                $('body').trigger('processStop');
            }
        });
    }

    window.openModal = openModal;
    window.closeCommentArea = closeCommentArea;
    window.updateComment = updateComment;
});
