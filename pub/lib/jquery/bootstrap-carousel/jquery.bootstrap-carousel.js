/* ==========================================================
 * bootstrap-carousel.js v2.2.2
 * http: //twitter.github.com/bootstrap/javascript.html#carousel
 * ==========================================================
 * Copyright 2012 Twitter, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http: //www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * ========================================================== */


!function ($) {

    "use strict"; // jshint ;_;


    /* CAROUSEL CLASS DEFINITION
     * ========================= */

    var Carousel = function (element, options) {
        this.$element = $(element);
        this.options = options;
//    this.options.pause == 'hover' && this.$element
//      .on('mouseenter', $.proxy(this.pause, this))
//      .on('mouseleave', $.proxy(this.cycle, this))
    };

    Carousel.prototype = {
        init: function () {
            var carousel = this.$element,
                options = this.options,
                href = '#' + carousel.context.id,
                itemClass = options.itemClass,
                arrowLeft = $('<a/>', {
                    'class': options.carouselLeftArrowClass,
                    href: href,
                    title: options.carouselLeftArrowTitle,
                    text: options.carouselLeftArrowText,
                    attr: {'data-slide': 'prev'}
                }),
                arrowRight = $('<a/>', {
                    'class': options.carouselRightArrowClass,
                    href: href,
                    title: options.carouselRightArrowTitle,
                    text: options.carouselRightArrowText,
                    attr: {'data-slide': 'next'}
                }),
                innerWrapper = $('<div/>', {
                    'class': options.carouselWrapperClass
                }),
                carouselSize = carousel.find('.' + itemClass).length,
                paginator = $('<ul/>', {
                    'class': options.paginatorClass
                });

            for (var i = 0; i < carouselSize; i++) {
                paginator.append($('<li/>', {
                    text: (i + 1),
                    title: (i + 1)
                }));
            }

            paginator
                .find('li:first')
                .addClass(options.activeClass);

            carousel
                .find('.' + itemClass)
                .addClass(options.itemClassDefault)
                .wrapAll(innerWrapper);

            carousel
                .addClass(options.carouselClass)
                .find('.' + itemClass + ':first')
                .addClass(options.activeClass)
                .end()
                .append(arrowLeft, paginator, arrowRight);

            return this;
        }, cycle: function (e) {
            if (!e) this.paused = false;
            this.options.interval && !this.paused && (this.interval = setInterval($.proxy(this.next, this), this.options.interval));

            return this;
        }, to: function (pos) {
            var itemClass = this.options.itemClass,
                $active = this.$element.find('.' + itemClass + '.' + this.options.activeClass),
                children = $active.parent().children(),
                activePos = children.index($active),
                that = this;

            if (pos > (children.length - 1) || pos < 0) return;

            if (this.sliding) {
                return this.$element.one('slid', function () {
                    that.to(pos);
                })
            }

            if (activePos == pos) {
                return this.pause().cycle();
            }

            return this.slide(pos > activePos ? 'next' :  'prev', $(children[pos]));
        }, pause: function (e) {
            if (!e) this.paused = true;
            if (this.$element.find('.next, .prev').length && $.support.transition.end) {
                this.$element.trigger($.support.transition.end);
                this.cycle();
            }
            clearInterval(this.interval);
            this.interval = null;
            return this;
        }, next: function () {
            if (this.sliding) return;
            return this.slide('next');
        }, prev: function () {
            if (this.sliding) return;
            return this.slide('prev');
        }, slide: function (type, next) {
            var itemClass = this.options.itemClass,
                activeClass = this.options.activeClass,
                $active = this.$element.find('.' + itemClass + '.' + activeClass),
                $next = next || $active[type](),
                $paginator = this.$element.find('.' + this.options.paginatorClass),
                isCycling = this.interval,
                direction = type == 'next' ? 'left' : 'right',
                fallback = type == 'next' ? 'first' : 'last',
                that = this, e;

            this.sliding = true;

            isCycling && this.pause();

            $next = $next.length ? $next :  this.$element.find('.' + itemClass)[fallback]();

            e = $.Event('slide', {
                relatedTarget: $next[0]
            });

            if ($next.hasClass(activeClass)) return;

            this.$element.trigger(e);
            if (e.isDefaultPrevented()) return;

            if ($.support.transition && this.options.effectSlide) {
                $next.addClass(type);
                $next[0].offsetWidth; // force reflow
                $active.addClass(direction);
                $next.addClass(direction);
                this.$element.one($.support.transition.end, function () {
                    $next.removeClass([type, direction].join(' ')).addClass(activeClass);
                    $active.removeClass([activeClass, direction].join(' '));
                    that.sliding = false;
                    setTimeout(function () {
                        that.$element.trigger('slid');
                    }, 0);
                })
            } else if (!$.support.transition && this.options.effectSlide) {
                $active.animate({left: (direction == 'right' ? '100%' :  '-100%')}, 600, function () {
                    $active.removeClass(activeClass);
                    that.sliding = false;
                    setTimeout(function () {
                        that.$element.trigger('slid');
                    }, 0)
                });
                $next.addClass(type).css({left: (direction == 'right' ? '-100%' :  '100%')}).animate({left: '0'}, 600, function () {
                    $next.removeClass(type).addClass(activeClass);
                })
            } else {
                $active.removeClass(activeClass);
                $next.addClass(activeClass);

                this.sliding = false;
                this.$element.trigger('slid');
            }

            $paginator
                .find('li')
                .eq($next.index())
                .addClass(activeClass)
                .siblings()
                .removeClass(activeClass);

            isCycling && this.cycle();

            return this
        }

    };


    /* CAROUSEL PLUGIN DEFINITION
     * ========================== */

    var old = $.fn.carousel;

    $.fn.carousel = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('carousel'),
                options = $.extend({}, $.fn.carousel.defaults, typeof option == 'object' && option),
                action = typeof option == 'string' ? option :  options.slide;

            if (!data) {
                $this.data('carousel', (data = new Carousel(this, options).init()));
            }
            if (typeof option == 'number') {
                data.to(option);
            } else if (action) {
                data[action]();
            } else if (options.interval) {
                data.cycle();
            }

            $this.keydown(function (e) {
                if (e.keyCode == 37) {
                    data.prev();
                }
                if (e.keyCode == 39) {
                    data.next();
                }
            });
            $this.find('.' + options.paginatorClass + ' li')
                .on('click.SlideTo', function () {
                    var elem = $(this),
                        slideTo = elem.index();

                    elem
                        .addClass(options.activeClass)
                        .siblings()
                        .removeClass(options.activeClass);

                    $this.carousel(slideTo);
                })

        })
    };

    $.fn.carousel.defaults = {
        interval: 5000,
        pause: 'hover',
        itemClass: 'item',
        itemClassDefault: 'carousel-item',
        activeClass: 'active',
        carouselClass: 'carousel',
        paginatorClass: 'carousel-pager',
        carouselWrapperClass: 'carousel-items',
        carouselLeftArrowClass: 'carousel-previous',
        carouselRightArrowClass: 'carousel-next',
        carouselLeftArrowTitle: 'Previous',
        carouselRightArrowTitle: 'Next',
        carouselLeftArrowText: 'Previous',
        carouselRightArrowText: 'Next',
        effectSlide: true
    };

    $.fn.carousel.Constructor = Carousel;


    /* CAROUSEL NO CONFLICT
     * ==================== */

    $.fn.carousel.noConflict = function () {
        $.fn.carousel = old;
        return this;
    };

    /* CAROUSEL DATA-API
     * ================= */

    $(document).on('click.carousel.data-api', '[data-slide]', function (e) {
        var $this = $(this),
            href,
            $target = $($this.attr('data-target') || (href = $this.attr('href')) && href.replace(/.*(?=#[^\s]+$)/, '')),
            options = $.extend({}, $target.data(), $this.data());

        $target.carousel(options);
        e.preventDefault();
    })
}(window.jQuery);