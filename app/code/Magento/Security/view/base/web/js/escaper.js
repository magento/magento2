/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A loose JavaScript version of Magento\Framework\Escaper
 *
 * Due to differences in how XML/HTML is processed in PHP vs JS there are a couple of minor differences in behavior
 * from the PHP counterpart.
 *
 * The first difference is that the default invocation of escapeHtml without allowedTags will double-escape existing
 * entities as the intention of such an invocation is that the input isn't supposed to contain any HTML.
 *
 * The second difference is that escapeHtml will not escape quotes. Since the input is actually being processed by the
 * DOM there is no chance of quotes being mixed with HTML syntax. And, since escapeHtml is not
 * intended to be used with raw injection into a HTML attribute, this is acceptable.
 *
 * @api
 */
define([], function () {
    'use strict';

    return {
        neverAllowedElements: ['script', 'img', 'embed', 'iframe', 'video', 'source', 'object', 'audio'],
        generallyAllowedAttributes: ['id', 'class', 'href', 'title', 'style'],
        forbiddenAttributesByElement: {
            a: ['style']
        },

        /**
         * Escape a string for safe injection into HTML
         *
         * @param {String} data
         * @param {Array|null} allowedTags
         * @returns {String}
         */
        escapeHtml: function (data, allowedTags) {
            var domParser = new DOMParser(),
                fragment = domParser.parseFromString('<div></div>', 'text/html');

            fragment = fragment.body.childNodes[0];
            allowedTags = typeof allowedTags === 'object' && allowedTags.length ? allowedTags : null;

            if (allowedTags) {
                fragment.innerHTML = data || '';
                allowedTags = this._filterProhibitedTags(allowedTags);

                this._removeComments(fragment);
                this._removeNotAllowedElements(fragment, allowedTags);
                this._removeNotAllowedAttributes(fragment);

                return fragment.innerHTML;
            }

            fragment.textContent = data || '';

            return fragment.innerHTML;
        },

        /**
         * Remove the always forbidden tags from a list of provided tags
         *
         * @param {Array} tags
         * @returns {Array}
         * @private
         */
        _filterProhibitedTags: function (tags) {
            return tags.filter(function (n) {
                return this.neverAllowedElements.indexOf(n) === -1;
            }.bind(this));
        },

        /**
         * Remove comment nodes from the given node
         *
         * @param {Node} node
         * @private
         */
        _removeComments: function (node) {
            var treeWalker = node.ownerDocument.createTreeWalker(
                    node,
                    NodeFilter.SHOW_COMMENT,
                    function () {
                        return NodeFilter.FILTER_ACCEPT;
                    },
                    false
                ),
                nodesToRemove = [];

            while (treeWalker.nextNode()) {
                nodesToRemove.push(treeWalker.currentNode);
            }

            nodesToRemove.forEach(function (nodeToRemove) {
                nodeToRemove.parentNode.removeChild(nodeToRemove);
            });
        },

        /**
         * Strip the given node of all disallowed tags while permitting any nested text nodes
         *
         * @param {Node} node
         * @param {Array|null} allowedTags
         * @private
         */
        _removeNotAllowedElements: function (node, allowedTags) {
            var treeWalker = node.ownerDocument.createTreeWalker(
                    node,
                    NodeFilter.SHOW_ELEMENT,
                    function (currentNode) {
                        return allowedTags.indexOf(currentNode.nodeName.toLowerCase()) === -1 ?
                            NodeFilter.FILTER_ACCEPT
                            // SKIP instead of REJECT because REJECT also rejects child nodes
                            : NodeFilter.FILTER_SKIP;
                    },
                false
                ),
                nodesToRemove = [];

            while (treeWalker.nextNode()) {
                if (allowedTags.indexOf(treeWalker.currentNode.nodeName.toLowerCase()) === -1) {
                    nodesToRemove.push(treeWalker.currentNode);
                }
            }

            nodesToRemove.forEach(function (nodeToRemove) {
                nodeToRemove.parentNode.replaceChild(
                    node.ownerDocument.createTextNode(nodeToRemove.textContent),
                    nodeToRemove
                );
            });
        },

        /**
         * Remove any invalid attributes from the given node
         *
         * @param {Node} node
         * @private
         */
        _removeNotAllowedAttributes: function (node) {
            var treeWalker = node.ownerDocument.createTreeWalker(
                    node,
                    NodeFilter.SHOW_ELEMENT,
                    function () {
                        return NodeFilter.FILTER_ACCEPT;
                    },
                false
                ),
                i,
                attribute,
                nodeName,
                attributesToRemove = [];

            while (treeWalker.nextNode()) {
                for (i = 0; i < treeWalker.currentNode.attributes.length; i++) {
                    attribute = treeWalker.currentNode.attributes[i];
                    nodeName = treeWalker.currentNode.nodeName.toLowerCase();

                    if (this.generallyAllowedAttributes.indexOf(attribute.name) === -1  || // eslint-disable-line max-depth,max-len
                        this._checkHrefValue(attribute) ||
                        this.forbiddenAttributesByElement[nodeName] &&
                        this.forbiddenAttributesByElement[nodeName].indexOf(attribute.name) !== -1
                    ) {
                        attributesToRemove.push(attribute);
                    }
                }
            }

            attributesToRemove.forEach(function (attributeToRemove) {
                attributeToRemove.ownerElement.removeAttribute(attributeToRemove.name);
            });
        },

        /**
         * Check that attribute contains script content
         *
         * @param {Object} attribute
         * @private
         */
        _checkHrefValue: function (attribute) {
            return attribute.nodeName === 'href' && attribute.nodeValue.startsWith('javascript');
        }
    };
});
