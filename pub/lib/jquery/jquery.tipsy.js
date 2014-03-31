// tipsy, facebook style tooltips for jquery
// http://onehackoranother.com/projects/jquery/tipsy/
// version 1.0.0a
// (c) 2008-2010 jason frame [jason@onehackoranother.com]
// releated under the MIT license

;(function($) {
    'use strict';

    function fixTitle($ele) {
        if ($ele.attr('title') || typeof($ele.attr('original-title')) != 'string') {
            $ele.attr('original-title', $ele.attr('title') || '').removeAttr('title');
        }
    }
    
    function Tipsy(element, options) {
        this.$element = $(element);
        this.options = options;
        this.enabled = true;
        fixTitle(this.$element);

        if ($(element).attr('tooltip-target') && $(element).attr('tooltip-target').length) {
            this.$element = $($(element).attr('tooltip-target'));
        }
    }
    
    Tipsy.prototype = {
        show: function(params) {
            var title = this.getTitle();
            if (title && this.enabled) {
                var $tip = this.tip();
                
                $tip.find('.tipsy-inner')[this.options.html ? 'html' : 'text'](title);
                $tip[0].className = 'tipsy'; // reset classname in case of dynamic tooltipPos
                $tip.remove().css({top: 0, left: 0, visibility: 'hidden', display: 'block'}).appendTo(document.body);
                
                var pos = $.extend({}, this.$element.offset(), {
                    width: this.$element[0].offsetWidth,
                    height: this.$element[0].offsetHeight
                });
                
                var actualWidth = $tip[0].offsetWidth, actualHeight = $tip[0].offsetHeight;
                var tooltipPosition = (typeof this.options.tooltipPos == 'function')
                                ? this.options.tooltipPos.call(this.$element[0])
                                : this.options.tooltipPos;

                var tp;
                switch (tooltipPosition.charAt(0)) {
                    case 's':
                        tp = {top: pos.top + pos.height + this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 'n':
                        tp = {top: pos.top - actualHeight - this.options.offset, left: pos.left + pos.width / 2 - actualWidth / 2};
                        break;
                    case 'w':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left - actualWidth - this.options.offset};
                        break;
                    case 'e':
                        tp = {top: pos.top + pos.height / 2 - actualHeight / 2, left: pos.left + pos.width + this.options.offset};
                        break;
                }
                
                if (tooltipPosition.length == 2) {
                    if (tooltipPosition.charAt(1) == 'e') {
                        tp.left = pos.left + pos.width / 2 - 15;
                    } else {
                        tp.left = pos.left + pos.width / 2 - actualWidth + 15;
                    }
                }

                $tip.css(tp).addClass('tipsy-' + tooltipPosition);
                
                if (this.options.fade) {
                    $tip.stop().css({opacity: 0, display: 'block', visibility: 'visible'}).animate({opacity: this.options.opacity});
                } else {
                    $tip.css({visibility: 'visible', opacity: this.options.opacity});
                }
            }
        },
        
        hide: function() {
            if (this.options.fade) {
                this.tip().stop().fadeOut(function() { $(this).remove(); });
            } else {
                this.tip().remove();
            }
        },
        
        getTitle: function() {
            var title, $e = this.$element, o = this.options;
            fixTitle($e);
            var title, o = this.options;
            if (typeof o.title == 'string') {
                title = $e.attr(o.title == 'title' ? 'original-title' : o.title);
            } else if (typeof o.title == 'function') {
                title = o.title.call($e[0]);
            }
            title = ('' + title).replace(/(^\s*|\s*$)/, "");
            return title || o.fallback;
        },
        
        tip: function() {
            if (!this.$tip) {
                this.$tip = $('<div class="tipsy"></div>').html('<div class="tipsy-arrow"></div><div class="tipsy-inner"/></div>');
            }
            return this.$tip;
        },
        
        validate: function() {
            if (!this.$element[0].parentNode) {
                this.hide();
                this.$element = null;
                this.options = null;
            }
        },
        
        enable: function() { this.enabled = true; },
        disable: function() { this.enabled = false; },
        toggleEnabled: function() { this.enabled = !this.enabled; }
    };
    
    $.fn.tipsy = function(options) {
        if (options === true) {
            return this.data('tipsy');
        } else if (typeof options == 'string') {
            return this.data('tipsy')[options]();
        }
        
        options = $.extend({}, $.fn.tipsy.defaults, options);
        
        function get(ele) {
            var tipsy = $.data(ele, 'tipsy');
            if (!tipsy) {
                tipsy = new Tipsy(ele, $.fn.tipsy.elementOptions(ele, options));
                $.data(ele, 'tipsy', tipsy);
            }
            return tipsy;
        }
        
        function enter() {
            var tipsy = get(this);
            tipsy.hoverState = 'in';
            if (options.delayIn == 0) {
                tipsy.show();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'in') tipsy.show(); }, options.delayIn);
            }
        }
        
        function leave() {
            var tipsy = get(this);
            tipsy.hoverState = 'out';
            if (options.delayOut == 0) {
                tipsy.hide();
            } else {
                setTimeout(function() { if (tipsy.hoverState == 'out') tipsy.hide(); }, options.delayOut);
            }
        }
        
        if (!options.live) this.each(function() { get(this); });
        
        if (options.trigger != 'manual') {
            var binder   = options.live ? 'live' : 'bind',
                eventIn  = options.trigger == 'hover' ? 'mouseenter' : 'focus',
                eventOut = options.trigger == 'hover' ? 'mouseleave' : 'blur';
            this[binder](eventIn, enter)[binder](eventOut, leave);
        }
        
        return this;
        
    };
    
    // Overwrite this method to provide options on a per-element basis.
    // For example, you could store the tooltipPos in a 'tipsy-tooltipPos' attribute:
    // return $.extend({}, options, {tooltipPos: $(ele).attr('tipsy-tooltipPos') || 'n' });
    // (remember - do not modify 'options' in place!)
    $.fn.tipsy.elementOptions = function(ele, options) {
        return $.metadata ? $.extend({}, options, $(ele).metadata()) : options;
    };
    
    $.fn.tipsy.autoNS = function() {
        return $(this).offset().top > ($(document).scrollTop() + $(window).height() / 2) ? 's' : 'n';
    };
    
    $.fn.tipsy.autoWE = function() {
        return $(this).offset().left > ($(document).scrollLeft() + $(window).width() / 2) ? 'w' : 'e';
    };

    $.fn.tipsy.auto = function(element) {
        var elem = element,
            offset = elem.offset(),
            fLeft, fRight, fTop, fBottom;

        fLeft = offset.left - $(document).scrollLeft();
        fRight = $(window).width() - (offset.left - $(document).scrollLeft() + elem.outerWidth(true));
        fTop = offset.top - $(document).scrollTop();
        fBottom = $(window).height() - (offset.top - $(document).scrollTop() + elem.outerHeight(true));

        var freeSpaces = [
            {
                name:'n',
                value:fTop
            },
            {
                name:'w',
                value:fLeft
            },
            {
                name:'s',
                value:fBottom
            },
            {
                name:'e',
                value:fRight
            },
        ].sort(function (a,b) {return a.value - b.value;});

        var majority = {
            'n': 2,
            'w': 1,
            's': 2,
            'e': 4
        };

        for (var i=0, len=freeSpaces.length; i<len; i++) {
            var item = freeSpaces[i];

            item.coof = (i + 1) * majority[item.name];
        }
        return freeSpaces.sort(function(a,b) {return a.coof - b.coof;})[freeSpaces.length - 1].name;
    };

    $.fn.tipsy.defaults = {
        delayIn: 0,
        delayOut: 0,
        fade: false,
        fallback: '',
        tooltipPos: 's',
        html: true,
        live: false,
        offset: 0,
        opacity: 1,
        title: 'title',
        trigger: 'hover'
    };
})(jQuery);