/*!
 * jQuery UI Sortable
 * http://jqueryui.com
 *
 * Copyright jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */

define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * Patch for sortable widget.
     * Can safely remove only when jQuery UI is upgraded to >= 1.13.1.
     * Fixes:
     * https://github.com/jquery/jquery-ui/pull/2008
     * https://github.com/jquery/jquery-ui/pull/2009
     */
    var sortablePatch = {
        /** @inheritdoc */
        _mouseDrag: function (event) {
            var i, item, itemElement, intersection,
                o = this.options;

            //Compute the helpers position
            this.position = this._generatePosition(event);
            this.positionAbs = this._convertPositionTo("absolute");

            //Set the helper position
            if (!this.options.axis || this.options.axis !== "y") {
                this.helper[0].style.left = this.position.left + "px";
            }
            if (!this.options.axis || this.options.axis !== "x") {
                this.helper[0].style.top = this.position.top + "px";
            }

            //Do scrolling
            if (o.scroll) {
                if (this._scroll(event) !== false) {

                    //Update item positions used in position checks
                    this._refreshItemPositions(true);

                    if ($.ui.ddmanager && !o.dropBehaviour) {
                        $.ui.ddmanager.prepareOffsets(this, event);
                    }
                }
            }

            this.dragDirection = {
                vertical: this._getDragVerticalDirection(),
                horizontal: this._getDragHorizontalDirection()
            };

            //Rearrange
            for (i = this.items.length - 1; i >= 0; i--) {

                //Cache variables and intersection, continue if no intersection
                item = this.items[i];
                itemElement = item.item[0];
                intersection = this._intersectsWithPointer(item);
                if (!intersection) {
                    continue;
                }

                // Only put the placeholder inside the current Container, skip all
                // items from other containers. This works because when moving
                // an item from one container to another the
                // currentContainer is switched before the placeholder is moved.
                //
                // Without this, moving items in "sub-sortables" can cause
                // the placeholder to jitter between the outer and inner container.
                if (item.instance !== this.currentContainer) {
                    continue;
                }

                // Cannot intersect with itself
                // no useless actions that have been done before
                // no action if the item moved is the parent of the item checked
                if (itemElement !== this.currentItem[0] &&
                    this.placeholder[intersection === 1 ?
                        "next" : "prev"]()[0] !== itemElement &&
                    !$.contains(this.placeholder[0], itemElement) &&
                    (this.options.type === "semi-dynamic" ?
                            !$.contains(this.element[0], itemElement) :
                            true
                    )
                ) {

                    this.direction = intersection === 1 ? "down" : "up";

                    if (this.options.tolerance === "pointer" ||
                        this._intersectsWithSides(item)) {
                        this._rearrange(event, item);
                    } else {
                        break;
                    }

                    this._trigger("change", event, this._uiHash());
                    break;
                }
            }

            //Post events to containers
            this._contactContainers(event);

            //Interconnect with droppables
            if ($.ui.ddmanager) {
                $.ui.ddmanager.drag(this, event);
            }

            //Call callbacks
            this._trigger("sort", event, this._uiHash());

            this.lastPositionAbs = this.positionAbs;
            return false;

        },

        /** @inheritdoc */
        refreshPositions: function (fast) {

            // Determine whether items are being displayed horizontally
            this.floating = this.items.length ?
                this.options.axis === "x" || this._isFloating(this.items[0].item) :
                false;

            // This has to be redone because due to the item being moved out/into the offsetParent,
            // the offsetParent's position will change
            if (this.offsetParent && this.helper) {
                this.offset.parent = this._getParentOffset();
            }

            this._refreshItemPositions(fast);

            var i, p;

            if (this.options.custom && this.options.custom.refreshContainers) {
                this.options.custom.refreshContainers.call(this);
            } else {
                for (i = this.containers.length - 1; i >= 0; i--) {
                    p = this.containers[i].element.offset();
                    this.containers[i].containerCache.left = p.left;
                    this.containers[i].containerCache.top = p.top;
                    this.containers[i].containerCache.width =
                        this.containers[i].element.outerWidth();
                    this.containers[i].containerCache.height =
                        this.containers[i].element.outerHeight();
                }
            }

            return this;
        },

        /** @inheritdoc */
        _contactContainers: function (event) {
            var i, j, dist, itemWithLeastDistance, posProperty, sizeProperty, cur, nearBottom,
                floating, axis,
                innermostContainer = null,
                innermostIndex = null;

            // Get innermost container that intersects with item
            for (i = this.containers.length - 1; i >= 0; i--) {

                // Never consider a container that's located within the item itself
                if ($.contains(this.currentItem[0], this.containers[i].element[0])) {
                    continue;
                }

                if (this._intersectsWith(this.containers[i].containerCache)) {

                    // If we've already found a container and it's more "inner" than this, then continue
                    if (innermostContainer &&
                        $.contains(
                            this.containers[i].element[0],
                            innermostContainer.element[0])) {
                        continue;
                    }

                    innermostContainer = this.containers[i];
                    innermostIndex = i;

                } else {

                    // container doesn't intersect. trigger "out" event if necessary
                    if (this.containers[i].containerCache.over) {
                        this.containers[i]._trigger("out", event, this._uiHash(this));
                        this.containers[i].containerCache.over = 0;
                    }
                }

            }

            // If no intersecting containers found, return
            if (!innermostContainer) {
                return;
            }

            // Move the item into the container if it's not there already
            if (this.containers.length === 1) {
                if (!this.containers[innermostIndex].containerCache.over) {
                    this.containers[innermostIndex]._trigger("over", event, this._uiHash(this));
                    this.containers[innermostIndex].containerCache.over = 1;
                }
            } else {

                // When entering a new container, we will find the item with the least distance and
                // append our item near it
                dist = 10000;
                itemWithLeastDistance = null;
                floating = innermostContainer.floating || this._isFloating(this.currentItem);
                posProperty = floating ? "left" : "top";
                sizeProperty = floating ? "width" : "height";
                axis = floating ? "pageX" : "pageY";

                for (j = this.items.length - 1; j >= 0; j--) {
                    if (!$.contains(
                        this.containers[innermostIndex].element[0], this.items[j].item[0])
                    ) {
                        continue;
                    }
                    if (this.items[j].item[0] === this.currentItem[0]) {
                        continue;
                    }

                    cur = this.items[j].item.offset()[posProperty];
                    nearBottom = false;
                    if (event[axis] - cur > this.items[j][sizeProperty] / 2) {
                        nearBottom = true;
                    }

                    if (Math.abs(event[axis] - cur) < dist) {
                        dist = Math.abs(event[axis] - cur);
                        itemWithLeastDistance = this.items[j];
                        this.direction = nearBottom ? "up" : "down";
                    }
                }

                //Check if dropOnEmpty is enabled
                if (!itemWithLeastDistance && !this.options.dropOnEmpty) {
                    return;
                }

                if (this.currentContainer === this.containers[innermostIndex]) {
                    if (!this.currentContainer.containerCache.over) {
                        this.containers[innermostIndex]._trigger("over", event, this._uiHash());
                        this.currentContainer.containerCache.over = 1;
                    }
                    return;
                }

                if (itemWithLeastDistance) {
                    this._rearrange(event, itemWithLeastDistance, null, true);
                } else {
                    this._rearrange(event, null, this.containers[innermostIndex].element, true);
                }
                this._trigger("change", event, this._uiHash());
                this.containers[innermostIndex]._trigger("change", event, this._uiHash(this));
                this.currentContainer = this.containers[innermostIndex];

                //Update the placeholder
                this.options.placeholder.update(this.currentContainer, this.placeholder);

                //Update scrollParent
                this.scrollParent = this.placeholder.scrollParent();

                //Update overflowOffset
                if (this.scrollParent[0] !== this.document[0] &&
                    this.scrollParent[0].tagName !== "HTML") {
                    this.overflowOffset = this.scrollParent.offset();
                }

                this.containers[innermostIndex]._trigger("over", event, this._uiHash(this));
                this.containers[innermostIndex].containerCache.over = 1;
            }

        }
    }

    return function () {
        var majorVersion = parseInt($.ui.version.split('.')[0]),
            minorVersion = parseInt($.ui.version.split('.')[1]),
            patchVersion = parseInt($.ui.version.split('.')[2])

        if (majorVersion === 1 && minorVersion === 13 && patchVersion > 0 ||
            majorVersion === 1 && minorVersion >= 14 ||
            majorVersion >= 2
        ) {
            console.warn('jQuery ui sortable patch is no longer necessary, and should be removed');
        }

        $.widget('ui.sortable', $.ui.sortable, sortablePatch);
    };

});
