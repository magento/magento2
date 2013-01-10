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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

( function ( $ ) {

    $.widget('vde.infinite_scroll', {
        _locked: false,
        _loader: '.theme-loader',
        _container: '.theme-container',
        _defaultElementSize: 400,
        _elementsInRow: 2,
        _pageSize: 4,
        options: {
            url: '',
            loadDataOnCreate: true
        },

        /**
         * Load data
         * @public
         */
        loadData: function() {
            if (this._isLocked()) {
                return
            }
            this.setLocked(true);

            $.ajax({
                url: this.options.url,
                type: 'GET',
                dataType: 'JSON',
                data: { 'page_size': this._pageSize },
                context: $(this),
                success: $.proxy(function(data) {
                    if (data.content) {
                        if (this.options.url === '') {
                            this.setLocked(false);
                            return;
                        }
                        this.element.find(this._container).append(data.content);
                        this.setLocked(false);
                    }

                    var eventData = {};
                    this.element.trigger('loaded', eventData);
                }, this),
                error: $.proxy(function() {
                    this.options.url = '';
                    throw Error($.mage.__('Some problem with theme loading'));
                }, this)
            });
        },

        /**
         * Set is locked
         * @param {boolean} status locked status
         * @protected
         */
        setLocked: function(status) {
            (status) ? $(this._loader).show() : $(this._loader).hide();
            this._locked = status;
        },

        /**
         * Load data is container empty
         * @public
         */
        loadDataIsContainerEmpty: function() {
            if ($(this._container).children().length == 0) {
                this.loadData();
            }
        },

        /**
         * Infinite scroll creation
         * @protected
         */
        _create: function() {
            if (this.element.find(this._container).children().length == 0) {
                this._pageSize = this._calculatePagesSize();
            }

            this._bind();
        },

        /**
         * Calculate default pages count
         *
         * @return {number}
         * @protected
         */
        _calculatePagesSize: function() {
            elementsCount = Math.ceil($(window).height() / this._defaultElementSize) * this._elementsInRow;
            return (elementsCount % 2) ? elementsCount++ : elementsCount;
        },

        /**
         * Get is locked
         * @return {boolean}
         * @protected
         */
        _isLocked: function() {
            return this._locked;
        },

        /**
         * Bind handlers
         * @protected
         */
        _bind: function() {
            if (this.options.loadDataOnCreate) {
                $(document).ready(
                    $.proxy(this.loadData, this)
                );
            }

            $(window).resize(
                $.proxy(function(event) {
                    if (this._isScrolledBottom() && this.options.url) {
                        this.loadData();
                    }
                }, this)
            );

            $(window).scroll(
                $.proxy(function(event) {
                    if (this._isScrolledBottom() && this.options.url) {
                        this.loadData();
                    }
                }, this)
            );
        },

        /**
         * Check is scrolled bottom
         * @return {boolean}
         * @protected
         */
        _isScrolledBottom: function() {
            return ($(window).scrollTop() + $(window).height() >= $(document).height() - this._defaultElementSize)
        }
    });

})(jQuery);
