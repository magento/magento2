define([
    'jquery',
    'mage/translate'
// eslint-disable-next-line strict
], function ($) {
    'use strict';

    window.openModal = openModal;
    window.closeCommentArea = closeCommentArea;
    window.updateComment = updateComment;

    /**
     * Open edit comment modal
     *
     * @param event
     */
    function openModal(event){
        $('.edit-comment-container').css('display','none');
        $('.note-list-comment').css('display','block');
        $('.edit-comment-textarea').attr('disabled','disabled');

        let element =  $(event.target);
        let noteListContainer = element.parent('.note-list-customer');
        let editCommentContainer = noteListContainer.siblings('.edit-comment-container');
        let noteListCommentContainer = noteListContainer.siblings('.note-list-comment');
        let commentText = noteListCommentContainer.text();

        noteListCommentContainer.css('display','none');
        editCommentContainer.css('display','block')

        let editCommentTextarea = editCommentContainer.find('.edit-comment-textarea')
        editCommentTextarea.removeAttr('disabled');
        editCommentTextarea.val($.trim(commentText));
    }

    /**
     * Close edit comment modal
     */
    function closeCommentArea(){
        $(".edit-comment-container").css('display','none');
        $(".note-list-comment").css('display','block');
    }

    /**
     * Update sales entity comment
     *
     * @param url
     * @param event
     */
    function updateComment(url, event) {
        let data = {};
        let element =  $(event.target);

        data['comment'] = {
            'form_key' : window.FORM_KEY,
            'comment_id' : element.attr('data-comment-id'),
            'comment' : element.parent().parent('.edit-comment-container').find('.edit-comment-textarea').val()
        };

        $('body').trigger('processStart');

        $.ajax({
            url: url,
            data: data,
            success: function (response) {
                if (response.error) {
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
});
