/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* global Base64 */
define([
    'jquery',
    'uiComponent',
    'uiLayout',
    'underscore',
    'Magento_MediaGalleryUi/js/directory/actions/createDirectory',
    'jquery/jstree/jquery.jstree',
    'Magento_Ui/js/lib/view/utils/async',
    'Magento_MediaGalleryUi/js/directory/directories'
], function ($, Component, layout, _, createDirectory) {
    'use strict';

    return Component.extend({
        defaults: {
            allowedActions: [],
            filterChipsProvider: 'componentType = filters, ns = ${ $.ns }',
            bookmarkProvider: 'componentType = bookmark, ns = ${ $.ns }',
            directoryTreeSelector: '#media-gallery-directory-tree',
            getDirectoryTreeUrl: 'media_gallery/directories/gettree',
            createDirectoryUrl: 'media_gallery/directories/create',
            deleteDirectoryUrl: 'media_gallery/directories/delete',
            jsTreeReloaded: null,
            modules: {
                bookmarks: '${ $.bookmarkProvider }',
                directories: '${ $.name }_directories',
                filterChips: '${ $.filterChipsProvider }'
            },
            listens: {
                '${ $.provider }:params.filters.path': 'updateSelectedDirectory'
            },
            viewConfig: [{
                component: 'Magento_MediaGalleryUi/js/directory/directories',
                name: '${ $.name }_directories',
                allowedActions: '${ $.allowedActions }'
            }]
        },

        /**
         * Initializes media gallery directories component.
         *
         * @returns {Sticky} Chainable.
         */
        initialize: function () {
            this._super().observe(['activeNode']).initView();

            $.async(
                this.directoryTreeSelector,
                this,
                function () {
                    this.initJsTreeEvents();
                    this.renderDirectoryTree().then(function () {
                        this.initEvents();
                    }.bind(this));
                }.bind(this)
            );

            return this;
        },

        /**
         * Render directory tree component.
         */
        renderDirectoryTree: function () {
            return this.getJsonTree().then(function (data) {
                this.createFolderIfNotExists(data).then(function (isFolderCreated) {
                    if (isFolderCreated) {
                        this.getJsonTree().then(function (newData) {
                            this.createTree(newData);
                        }.bind(this));
                    } else {
                        this.createTree(data);
                    }
                }.bind(this));
            }.bind(this));
        },

        /**
         * Set jstree reloaded
         *
         * @param {Boolean} value
         */
        setJsTreeReloaded: function (value) {
            this.jsTreeReloaded = value;
        },

        /**
         * Create folder by provided current_tree_path param
         *
         * @param {Array} directories
         */
        createFolderIfNotExists: function (directories) {
            var requestedDirectory = this.getRequestedDirectory(),
                deferred = $.Deferred(),
                pathArray;

            if (_.isNull(requestedDirectory)) {
                deferred.resolve(false);

                return deferred.promise();
            }

            if (this.isDirectoryExist(directories, requestedDirectory)) {
                deferred.resolve(false);

                return deferred.promise();
            }

            pathArray = this.convertPathToPathsArray(requestedDirectory);

            $.each(pathArray, function (i, val) {
                if (this.isDirectoryExist(directories, val)) {
                    pathArray.splice(i, 1);
                }
            }.bind(this));

            createDirectory(
                this.createDirectoryUrl,
                pathArray
            ).then(function () {
                deferred.resolve(true);
            });

            return deferred.promise();
        },

        /**
         * Verify if directory exists in array
         *
         * @param {Array} directories
         * @param {String} directoryId
         */
        isDirectoryExist: function (directories, directoryId) {
            var found = false;

            /**
             * Recursive search in array
             *
             * @param {Array} data
             * @param {String} id
             */
            function recurse(data, id) {
                var i;

                for (i = 0; i < data.length; i++) {
                    if (data[i].id === id) {
                        found = data[i];
                        break;
                    } else if (data[i].children && data[i].children.length) {
                        recurse(data[i].children, id);
                    }
                }
            }

            recurse(directories, directoryId);

            return found;
        },

        /**
         * Convert path string to path array e.g 'path1/path2' -> ['path1', 'path1/path2']
         *
         * @param {String} path
         */
        convertPathToPathsArray: function (path) {
            var pathsArray = [],
                pathString = '',
                paths = path.split('/');

            $.each(paths, function (i, val) {
                pathString += i >= 1 ? val : val + '/';
                pathsArray.push(i >= 1 ? pathString : val);
            });

            return pathsArray;
        },

        /**
         * Initialize child components
         *
         * @returns {Object}
         */
        initView: function () {
            layout(this.viewConfig);

            return this;
        },

        /**
         * Wait for condition then call provided callback
         */
        waitForCondition: function (condition, callback) {
            if (condition()) {
                setTimeout(function () {
                    this.waitForCondition(condition, callback);
                }.bind(this), 100);
            } else {
                callback();
            }
        },

        /**
         * Remove ability to multiple select on nodes
         */
        disableMultiselectBehavior: function () {
            $.jstree.defaults.core.multiple = false;
        },

        /**
         *  Handle jstree events
         */
        initEvents: function () {
            this.disableMultiselectBehavior();

            $(window).on('reload.MediaGallery', function () {
                this.getJsonTree().then(function (data) {
                    this.createFolderIfNotExists(data).then(function (isCreated) {
                        if (isCreated) {
                            this.renderDirectoryTree().then(function () {
                                this.setJsTreeReloaded(true);
                                this.initJsTreeEvents();
                            }.bind(this));
                        } else {
                            this.updateSelectedDirectory();
                        }
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        },

        /**
         * Fire event for jstree component
         */
        initJsTreeEvents: function () {
            $(this.directoryTreeSelector).on('select_node.jstree', function (element, data) {
                this.setActiveNodeFilter(data.node.id);
                this.setJsTreeReloaded(false);
            }.bind(this));

            $(this.directoryTreeSelector).on('loaded.jstree', function () {
                this.updateSelectedDirectory();
            }.bind(this));
        },

        /**
         * Verify directory filter on init event, select folder per directory filter state
         */
        updateSelectedDirectory: function () {
            var currentFilterPath = this.filterChips().filters.path,
                requestedDirectory = this.getRequestedDirectory(),
                currentTreePath;

            if (_.isUndefined(currentFilterPath)) {
                this.clearFiltersHandle();

                return;
            }

            if (!_.isUndefined(this.bookmarks())) {
                if (!_.size(this.bookmarks().getViewData(this.bookmarks().defaultIndex))) {
                    setTimeout(function () {
                        this.updateSelectedDirectory();
                    }.bind(this), 500);

                    return;
                }
            }
            currentTreePath = this.isFilterApplied(currentFilterPath) || _.isNull(requestedDirectory) ?
                currentFilterPath : requestedDirectory;

            if (this.folderExistsInTree(currentTreePath)) {
                this.locateNode(currentTreePath);
            } else {
                this.selectStorageRoot();
            }
        },

        /**
         * Verify if directory exists in folder tree
         *
         * @param {String} path
         */
        folderExistsInTree: function (path) {
            if (!_.isUndefined(path)) {
                return $(this.directoryTreeSelector).jstree('get_node', path);
            }

            return false;
        },

        /**
         * Get requested directory from MediabrowserUtility
         *
         * @returns {String|null}
         */
        getRequestedDirectory: function () {
            return !_.isUndefined(window.MediabrowserUtility) && window.MediabrowserUtility.pathId !== '' ?
                Base64.idDecode(window.MediabrowserUtility.pathId) : null;
        },

        /**
         * Check if need to select directory by filters state
         *
         * @param {String} currentFilterPath
         */
        isFilterApplied: function (currentFilterPath) {
            return !_.isUndefined(currentFilterPath) && currentFilterPath !== '';
        },

        /**
         * Locate and higlight node in jstree by path id.
         *
         * @param {String} path
         */
        locateNode: function (path) {
            if ($(this.directoryTreeSelector).jstree('is_selected', path)) {
                return;
            }
            $(this.directoryTreeSelector).jstree('deselect_node',
                $(this.directoryTreeSelector).jstree('get_selected')
            );
            $(this.directoryTreeSelector).jstree('open_node', path);
            $(this.directoryTreeSelector).jstree('select_node', path, true);

        },

        /**
         * Clear filters
         */
        clearFiltersHandle: function () {
            $(this.directoryTreeSelector).jstree('deselect_all');
            this.activeNode(null);
            this.directories().setInActive();
        },

        /**
         * Set active node filter, or deselect if the same node clicked
         *
         * @param {String} nodePath
         */
        setActiveNodeFilter: function (nodePath) {
            if (this.activeNode() === nodePath && !this.jsTreeReloaded) {
                this.selectStorageRoot();
            } else {
                this.selectFolder(nodePath);
            }
        },

        /**
         * Remove folders selection -> select storage root
         */
        selectStorageRoot: function () {
            var filters = {},
                applied = this.filterChips().get('applied');

            $(this.directoryTreeSelector).jstree('deselect_all');

            filters = $.extend(true, filters, applied);
            delete filters.path;
            this.filterChips().set('applied', filters);
            this.activeNode(null);
            this.waitForCondition(
                function () {
                    return _.isUndefined(this.directories());
                }.bind(this),
                function () {
                    this.directories().setInActive();
                }.bind(this)
            );
        },

        /**
         * Set selected folder
         *
         * @param {String} path
         */
        selectFolder: function (path) {
            this.activeNode(path);

            this.waitForCondition(
                function () {
                    return _.isUndefined(this.directories());
                }.bind(this),
                function () {
                    this.directories().setActive(path);
                }.bind(this)
            );

            this.applyFilter(path);
        },

        /**
         * Remove active node from directory tree, and select next
         */
        removeNode: function () {
            $(this.directoryTreeSelector).jstree('delete_node',
                $(this.directoryTreeSelector).jstree('get_selected')
            );
        },

        /**
         * Apply folder filter by path
         *
         * @param {String} path
         */
        applyFilter: function (path) {
            var filters = {},
                applied = this.filterChips().get('applied');

            filters = $.extend(true, filters, applied);
            filters.path = path;
            this.filterChips().set('applied', filters);
        },

        /**
         * Reload jstree and update jstree events
         */
        reloadJsTree: function () {
            var deferred = $.Deferred();

            this.getJsonTree().then(function (data) {
                $(this.directoryTreeSelector).jstree(true).settings.core.data = data;
                $(this.directoryTreeSelector).jstree(true).refresh(false, true);
                this.setJsTreeReloaded(true);
                deferred.resolve();
            }.bind(this));

            return deferred.promise();
        },

        /**
         * Get json data for jstree
         */
        getJsonTree: function () {
            var deferred = $.Deferred();

            $.ajax({
                url: this.getDirectoryTreeUrl,
                type: 'GET',
                dataType: 'json',

                /**
                 * Success handler for request
                 *
                 * @param {Object} data
                 */
                success: function (data) {
                    deferred.resolve(data);
                },

                /**
                 * Error handler for request
                 *
                 * @param {Object} jqXHR
                 * @param {String} textStatus
                 */
                error: function (jqXHR, textStatus) {
                    deferred.reject();
                    throw textStatus;
                }
            });

            return deferred.promise();
        },

        /**
         * Initialize directory tree
         *
         * @param {Array} data
         */
        createTree: function (data) {
            // jscs:disable requireCamelCaseOrUpperCaseIdentifiers
            $(this.directoryTreeSelector).jstree({
                plugins: [],
                checkbox: {
                    three_state: false,
                    cascade: ''
                },
                core: {
                    data: data,
                    check_callback: true,
                    themes: {
                        dots: false
                    }
                }
            });
            // jscs:enable requireCamelCaseOrUpperCaseIdentifiers
        }
    });
});
