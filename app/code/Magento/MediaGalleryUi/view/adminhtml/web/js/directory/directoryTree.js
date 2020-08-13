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
    'Magento_Ui/js/lib/view/utils/async'
], function ($, Component, layout, _, createDirectory) {
    'use strict';

    return Component.extend({
        defaults: {
            filterChipsProvider: 'componentType = filters, ns = ${ $.ns }',
            directoryTreeSelector: '#media-gallery-directory-tree',
            getDirectoryTreeUrl: 'media_gallery/directories/gettree',
            createDirectoryUrl: 'media_gallery/directories/create',
            activeNode: null,
            modules: {
                directories: '${ $.name }_directories',
                filterChips: '${ $.filterChipsProvider }'
            },
            listens: {
                '${ $.provider }:params.filters.path': 'selectTreeFolder'
            },
            viewConfig: [{
                component: 'Magento_MediaGalleryUi/js/directory/directories',
                name: '${ $.name }_directories'
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
                    if (!isFolderCreated) {
                        this.createTree(data);
                        return;
                    }

                    this.getJsonTree().then(function (newData) {
                        this.createTree(newData);
                    }.bind(this));
                }.bind(this));
            }.bind(this));
        },

        /**
         * Create folder by provided current_tree_path param
         *
         * @param {Array} directories
         */
        createFolderIfNotExists: function (directories) {
            var requestedDirectoryPath = this.getRequestedDirectory(),
                deferred = $.Deferred(),
                pathArray;

            if (!requestedDirectoryPath) {
                deferred.resolve(false);

                return deferred.promise();
            }

            if (this.isDirectoryExist(directories[0], requestedDirectoryPath)) {
                deferred.resolve(false);

                return deferred.promise();
            }

            pathArray = this.convertPathToPathsArray(requestedDirectoryPath);

            $.each(pathArray, function (index, directoryId) {
                if (this.isDirectoryExist(directories[0], directoryId)) {
                    pathArray.splice(index, 1);
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
         * @param {String} path
         */
        isDirectoryExist: function (directories, path) {
            var i;

            for (i = 0; i < directories.length; i++) {
                if (directories[i].attr.id === path
                    || directories[i].children
                    && directories[i].children.length
                    && this.isDirectoryExist(directories[i].children, path)
                ) {
                    return true;
                }
            }

            return false;
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
            $.jstree.defaults.ui['select_range_modifier'] = false;
            $.jstree.defaults.ui['select_multiple_modifier'] = false;
        },

        /**
         *  Handle jstree events
         */
        initEvents: function () {
            this.initJsTreeEvents();
            this.disableMultiselectBehavior();

            $(window).on('reload.MediaGallery', function () {
                this.renderDirectoryTree().then(function () {
                    this.initJsTreeEvents();
                }.bind(this));
            }.bind(this));
        },

        /**
         * Fire event for jstree component
         */
        initJsTreeEvents: function () {
            $(this.directoryTreeSelector).on('select_node.jstree', function (element, data) {
                this.toggleSelectedDirectory($(data.rslt.obj).data('path'));
            }.bind(this));

            $(this.directoryTreeSelector).on('loaded.jstree', function () {
                var path = this.getRequestedDirectory() || this.filterChips().filters.path;

                if (this.activeNode() !== path) {
                    this.selectFolder(path);
                }
            }.bind(this));
        },

        /**
         * Verify directory filter on init event, select folder per directory filter state
         */
        selectTreeFolder: function (path) {
            this.isFolderRendered(path) ? this.locateNode(path) : this.selectStorageRoot();
        },

        /**
         * Verify if directory exists in folder tree
         *
         * @param {String} path
         * @return {Boolean}
         */
        isFolderRendered: function (path) {
            return _.isUndefined(path) ? false : $('#' + path.replace(/\//g, '\\/')).length === 1;
        },

        /**
         * Get directory requested from MediabrowserUtility
         *
         * @return {String|Null}
         */
        getRequestedDirectory: function () {
            return !_.isUndefined(window.MediabrowserUtility) && window.MediabrowserUtility.pathId !== ''
                ? Base64.idDecode(window.MediabrowserUtility.pathId)
                : null;
        },

        /**
         * Locate and higlight node in jstree by path id.
         *
         * @param {String} path
         */
        locateNode: function (path) {
            if (path === $(this.directoryTreeSelector).jstree('get_selected').attr('id')) {
                return;
            }
            path = path.replace(/\//g, '\\/');
            $(this.directoryTreeSelector).jstree('open_node', '#' + path);
            $(this.directoryTreeSelector).jstree('select_node', '#' + path, true);
        },

        /**
         * Set active node filter, or deselect if the same node clicked
         *
         * @param {String} path
         */
        toggleSelectedDirectory: function (path) {
            this.activeNode() === path ? this.selectStorageRoot() : this.selectFolder(path);
        },

        /**
         * Remove folders selection -> select storage root
         */
        selectStorageRoot: function () {
            $(this.directoryTreeSelector).jstree('deselect_all');
            this.activeNode(null);

            this.waitForCondition(
                function () {
                    return _.isUndefined(this.directories());
                }.bind(this),
                function () {
                    this.directories().setInActive();
                }.bind(this)
            );

            this.dropFilter();
        },

        /**
         * Set selected folder
         *
         * @param {String} path
         */
        selectFolder: function (path) {
            if (_.isUndefined(path) || _.isNull(path)) {
                return;
            }

            this.waitForCondition(
                function () {
                    return _.isUndefined(this.directories());
                }.bind(this),
                function () {
                    this.directories().setActive(path);
                }.bind(this)
            );

            this.applyFilter(path);
            this.activeNode(path);
        },

        /**
         * Remove active node from directory tree, and select next
         */
        removeNode: function () {
            $(this.directoryTreeSelector).jstree('remove');
        },

        /**
         * Apply folder filter by path
         *
         * @param {String} path
         */
        applyFilter: function (path) {
            this.filterChips().set(
                'applied',
                $.extend(
                    true,
                    {},
                    this.filterChips().get('applied'),
                    {
                        path: path
                    }
                )
            );
        },

        /**
         * Drop path filter
         */
        dropFilter: function () {
            var filters = {},
                applied = this.filterChips().get('applied');

            filters = $.extend(true, filters, applied);
            delete filters.path;
            this.filterChips().set('applied', filters);
        },

        /**
         * Reload jstree and update jstree events
         */
        reloadJsTree: function () {
            var deferred = $.Deferred();

            this.getJsonTree().then(function (data) {
                this.createTree(data);
                this.initEvents();
                deferred.resolve();
            }.bind(this));

            return deferred.promise();
        },

        /**
         * Get json data for jstree
         */
        getJsonTree: function () {
            return $.ajax({
                url: this.getDirectoryTreeUrl,
                type: 'GET',
                dataType: 'json'
            });
        },

        /**
         * Initialize directory tree
         *
         * @param {Array} data
         */
        createTree: function (data) {
            $(this.directoryTreeSelector).jstree({
                plugins: ['json_data', 'themes', 'ui', 'crrm', 'types', 'hotkeys'],
                vcheckbox: {
                    'two_state': true,
                    'real_checkboxes': true
                },
                'json_data': {
                    data: data
                },
                hotkeys: {
                    space: this._changeState,
                    'return': this._changeState
                },
                types: {
                    'types': {
                        'disabled': {
                            'check_node': true,
                            'uncheck_node': true
                        }
                    }
                }
            });
        }
    });
});
