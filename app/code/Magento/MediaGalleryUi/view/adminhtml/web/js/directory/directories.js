/**
 * Copyright © Magento, Inc. All rights reserved.g
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'underscore',
    'Magento_Ui/js/modal/prompt',
    'Magento_MediaGalleryUi/js/directory/actions/createDirectory',
    'Magento_MediaGalleryUi/js/directory/actions/deleteDirectory',
    'mage/translate',
    'validation'
], function ($, Component, confirm, uiAlert, _, prompt, createDirectory, deleteDirectory, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            directoryTreeSelector: '#media-gallery-directory-tree',
            deleteButtonSelector: '#delete_folder',
            createFolderButtonSelector: '#create_folder',
            messageDelay: 5,
            selectedFolder: null,
            messagesName: 'media_gallery_listing.media_gallery_listing.messages',
            modules: {
                directoryTree: '${ $.parentName }.media_gallery_directories',
                messages: '${ $.messagesName }'
            }
        },

        /**
         * Initializes media gallery directories component.
         *
         * @returns {Sticky} Chainable.
         */
        initialize: function () {
            this._super().observe(['selectedFolder']);
            this.initEvents();

            return this;
        },

        /**
          * Initialize directories events
          */
        initEvents: function () {
            $(this.deleteButtonSelector).on('delete_folder', function () {
                this.deleteFolder();
            }.bind(this));

            $(this.createFolderButtonSelector).on('create_folder', function () {
                this.createFolder();
            }.bind(this));
        },

        /**
         * Show confirmation popup and create folder based on user input
         */
        createFolder: function () {
            this.getPrompt({
                title: $t('New Folder Name:'),
                content: '',
                actions: {
                    /**
                     * Confirm action
                     */
                    confirm: function (folderName) {
                        createDirectory(
                            this.directoryTree().createDirectoryUrl,
                            [this.getNewFolderPath(folderName)]
                        ).then(function () {
                            this.directoryTree().reloadJsTree().then(function () {
                                $(this.directoryTree().directoryTreeSelector).on('loaded.jstree', function () {
                                    this.directoryTree().locateNode(this.getNewFolderPath(folderName));
                                }.bind(this));
                            }.bind(this));
                        }.bind(this)).fail(function (error) {
                            uiAlert({
                                content: error
                            });
                        });
                    }.bind(this)
                },
                buttons: [{
                    text: $t('Cancel'),
                    class: 'action-secondary action-dismiss',

                    /**
                     * Close modal
                     */
                    click: function () {
                        this.closeModal();
                    }
                }, {
                    text: $t('Confirm'),
                    class: 'action-primary action-accept'
                }]
            });
        },

        /**
         * Return configured path for folder creation.
         *
         * @param {String} folderName
         * @returns {String}
         */
        getNewFolderPath: function (folderName) {
            if (_.isUndefined(this.selectedFolder()) || _.isNull(this.selectedFolder())) {
                return folderName;
            }

            return this.selectedFolder() + '/' + folderName;
        },

        /**
          * Return configured prompt with input field
          */
        getPrompt: function (data) {
                prompt({
                    title: $t(data.title),
                    content:  $t(data.content),
                    modalClass: 'media-gallery-folder-prompt',
                    validation: true,
                    validationRules: ['required-entry', 'validate-alphanum'],
                    attributesField: {
                        name: 'folder_name',
                        'data-validate': '{required:true, validate-alphanum}',
                        maxlength: '128'
                    },
                    attributesForm: {
                        novalidate: 'novalidate',
                        action: ''
                    },
                    context: this,
                    actions: data.actions,
                    buttons: data.buttons
                });
            },

        /**
          * Confirmation popup for delete folder action.
          */
        deleteFolder: function () {
            confirm({
                title: $t('Are you sure you want to delete this folder?'),
                modalClass: 'delete-folder-confirmation-popup',
                content: $t('The following folder is going to be deleted: %1')
                    .replace('%1', this.selectedFolder()),
                actions: {

                    /**
                      * Delete folder on button click
                      */
                    confirm: function () {
                        deleteDirectory(
                            this.directoryTree().deleteDirectoryUrl,
                            this.selectedFolder()
                        ).then(function () {
                            this.directoryTree().removeNode();
                            this.directoryTree().selectStorageRoot();
                            $(window).trigger('folderDeleted.enhancedMediaGallery');
                        }.bind(this)).fail(function (error) {
                            uiAlert({
                                content: error
                            });
                        });
                    }.bind(this)
                }
            });
        },

        /**
         * Set inactive all nodes, adds disable state to Delete Folder Button
         */
        setInActive: function () {
            this.selectedFolder(null);
            $(this.deleteButtonSelector).attr('disabled', true).addClass('disabled');
        },

        /**
         * Set active node, remove disable state from Delete Forlder button
         *
         * @param {String} folderId
         */
        setActive: function (folderId) {
            this.selectedFolder(folderId);
            $(this.deleteButtonSelector).removeAttr('disabled').removeClass('disabled');
        }
    });
});
