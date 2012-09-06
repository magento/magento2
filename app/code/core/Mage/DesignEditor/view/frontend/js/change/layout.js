/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE_AFL.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

( function ( $ ) {
    /**
     * Abstract change object
     */
    function abstractChange() {
        /**
         * Clean change data
         */
        var _cleanData = function() {
            this.postData = {};
        }

        return {
            type: null,
            data: null,
            status: 'active',
            getType: function() {
                if (!this.type) {
                    throw Error($.mage.__('Type of change is not defined'));
                }
                return this.type;
            },
            getStatus: function() {
                return status;
            },
            undo: function() {
                if (this.status == 'undone') {
                    throw Error($.mage.__("Can't undo change twice"));
                }
                alert('undo');
                this.status = 'undone';
            },
            getTitle: function() {
                throw Error($.mage.__('Method "getTitle" is not implemented'));
            },
            setData: function(data) {
                this.data = data;
            },
            getData: function() {
                return this.data;
            },
            getPostData: function() {
                throw Error($.mage.__('Method "getTitle" is not implemented'));
            },
            setActionData: function() {
                throw Error($.mage.__('Method "getTitle" is not implemented'));
            }
        };
    }

    /**
     * Layout change object
     */
    function layoutChange() {
        /**
         * Move action
         */
        var ACTION_MOVE = 'move';

        /**
         * Remove action
         */
        var ACTION_REMOVE = 'remove';

        /**
         * Type get post data
         */
        var TYPE_POST_DATA = 'getPostData';

        /**
         * Type set data
         */
        var TYPE_SET_ACTION = 'setAction';

        return {
            type: 'layout',
            getTitle: function() {
                var data = this.getData();
                var title;
                switch (data.action) {

                    case ACTION_MOVE:
                        if (data.origin.container == data.destination.container) {
                            title = $.mage.__('Block #block# sorted').replace('#block#', data.block);
                        } else {
                            title = $.mage.__('Block #block# moved').replace('#block#', data.block);
                        }
                        break;
                    case ACTION_REMOVE:
                        title = $.mage.__('Block #block# removed').replace('#block#', data.block);
                        break;
                }
                return title;
            },
            _executeActionByType: function(action, type, data) {
                switch (action) {
                    case ACTION_MOVE:
                        return this[ '_' + type + this._stringToTitleCase(action) ](data);
                        break;
                    case ACTION_REMOVE:
                        return this[ '_' + type + this._stringToTitleCase(action) ](data);
                        break;
                    default:
                        throw Error($.mage.__('Invalid action "#action#"').replace('#action#', action));
                }
            },
            /** @todo maybe we need to create global object for strings? */
            _stringToTitleCase: function(string) {
                return string.charAt(0).toUpperCase() + string.slice(1).toLowerCase()
            },
            setActionData: function(data) {
                this._executeActionByType(data.action_name, TYPE_SET_ACTION, data);
            },
            _setActionMove: function(data) {
                this.setData({
                    action: ACTION_MOVE,
                    block: data.element_name,
                    origin: {
                        container: null,
                        order: null
                    },
                    destination: {
                        container: data.action_data.container,
                        order: data.action_data.after
                    }
                });
            },
            _setActionRemove: function(data) {
                this.setData({
                    action: ACTION_REMOVE,
                    block: data.element_name,
                    origin: {
                        container: null,
                        order: null
                    },
                    destination: {
                        container: null,
                        order: null
                    }
                });
            },
            getPostData: function() {
                var data = this.getData();
                return this._executeActionByType(data.action, TYPE_POST_DATA, data);
            },
            _getPostDataMove: function(data) {
                return {
                    handle: 'current_handle',
                    change_type: this.type,
                    element_name: data.block,
                    action_name: ACTION_MOVE,
                    action_data: {
                        container: data.destination.container,
                        after: data.destination.order
                    }
                }
            },
            _getPostDataRemove: function(data) {
                return {
                    handle: 'current_handle',
                    change_type: this.type,
                    element_name: data.block,
                    action_name: ACTION_REMOVE
                }
            }
        };
    }

    /**
     * File change object
     */
    function fileChange() {
        this._getTitle = function() {
            return 'File change';
        };
        return {
            type: 'file'
        };
    }

    /**
     * Change factory
     */
    $.fn.changeFactory = (function() {
        /**
         * Data sender
         */
        var _dataSender;

        return {
            _init: function(baseUrl) {
                _dataSender = $.fn.changeFactory.dataSender;
                _dataSender._init(baseUrl);
            },
            post: function(data) {
                _dataSender.post(data);
            },
            getInstance: function(type) {
                switch(type) {
                    case 'layout':
                        var change = new layoutChange();
                        break;
                    case 'file':
                        var change = new fileChange();
                        break;
                    default:
                        throw Error($.mage.__('Invalid change type "#type#"').replace('#type#', type));
                }
                return $.extend(new abstractChange(), change);
            }
        }
    })();

    /**
     * Change factory data sender
     */
    $.fn.changeFactory.dataSender = (function() {
        /**
         * Save change url
         *
         */
        var changeUrl = 'design/editor/savechange/';

        /**
         * Lock save when sending in progress
         */
        var _isSaveLocked = false;

        /**
         * Queue for layout changes
         */
        var _queue = {};

        /**
         * Queue length
         */
        var _queueLength = 0;

        /**
         * Save change url
         */
        var _saveChangeUrl = '';

        /**
         * Add data to queue
         */
        var _addDataToQueue = function(data) {
            _queue[_queueLength] = data;
            _queueLength++;
        }

        /**
         * Clean queue
         */
        var _clearQueue = function() {
            _queueLength = 0;
            _queue = {};
        }

        /**
         * Send queue
         */
        var _sendQueue = function() {
            var data = _queue;
            _clearQueue();

            $.ajax({
                url: _saveChangeUrl,
                type: "post",
                dataType: 'json',
                data: data,
                success: function(data) {
                    if (data.success) {
                        $.each(data.success[0], function(revision, actionTitle) {
                            $.fn.history.add(revision, actionTitle);
                        });
                        if (_queueLength > 0) {
                            _sendQueue();
                            return;
                        }
                        _isSaveLocked = false;
                    } else if(data.error) {
                        _isSaveLocked = false;
                        alert(data.error[0]);
                    }
                },
                error: function(data) {
                    _isSaveLocked = false;
                    throw Error($.mage.__('Some problem with save action'));
                }
            });
        }

        return {
            _init: function(baseUrl) {
                _saveChangeUrl = baseUrl + changeUrl;
            },
            post: function(data) {
                _addDataToQueue(data);
                if (_isSaveLocked) {
                    return;
                }
                _isSaveLocked = true;
                _sendQueue();
            }
        }
    })();
})( jQuery );
