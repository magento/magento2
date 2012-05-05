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
 * @category    design
 * @package     default_iphone
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

 // Homepage categories and subcategories slider
document.observe("dom:loaded", function() {

    transEndEventNames = {
        'WebkitTransition' : 'webkitTransitionEnd',
        'MozTransition'    : 'transitionend',
        'OTransition'      : 'oTransitionEnd',
        'msTransition'     : 'MSTransitionEnd',
        'transition'       : 'transitionend'
    },
    transEndEventName = transEndEventNames[ Modernizr.prefixed('transition') ];

    function handler(position) {
        var lat = position.coords.latitude,
            lng = position.coords.longitude;

        //alert(latitude + ' ' + longitude);

        var geocoder = new google.maps.Geocoder();

        function codeLatLng() {
            var latlng = new google.maps.LatLng(lat, lng);
            geocoder.geocode({'latLng': latlng}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[0]) {
                        alert(results[0].formatted_address);
                    }
                } else {
                    alert("Geocoder failed due to: " + status);
                }
            });
        }

        //codeLatLng();

    }

    var loadMore = Class.create({
        initialize: function (list, href, pattern) {
            var that = this;
            
            this.list = list;
            this.list.insert({ after : '<div class="more"><span id="more_button" class="more-button">More</span></div>'});
            this.href = href.readAttribute('href');
            this.button = $('more_button');
            this.holder = new Element('div', { 'class': 'response-holder' });
            
            this.button.observe('click', function () {
                if ( !that.button.hasClassName('loading') ) {
                    new Ajax.Request(that.href, {
                        onCreate: function () {
                            that.button.addClassName('loading');
                        },
                        onComplete: function(response) {
                            if (200 == response.status) {
                                that.holder.update(response.responseText).select(pattern).each(function(elem) {
                                    that.list.insert({ bottom : elem });
                                });
                                that.href = that.holder.select('.next-page')[0].readAttribute('href');
                                that.button.removeClassName('loading');
                                if ( !that.href ) {
                                    that.button.up().remove();
                                }
                            }

                        }
                    });
                }
            });
        }
    });

    if ( $$('.c-list')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.c-list')[0],
            $$('.next-page')[0],
            '.c-list > li'
        )
    }
    
    if ( $$('.downloadable-products-history .list')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.downloadable-products-history .list')[0],
            $$('.next-page')[0],
            '.downloadable-products-history .list > li'
        )
    }
    
    if ( $$('.review-history .list')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.review-history .list')[0],
            $$('.next-page')[0],
            '.review-history .list > li'
        )
    }
    
    if ( $$('.recent-orders .data-table')[0] && $$('.next-page')[0]  ) {
        var loadMoreCategory = new loadMore(
            $$('.recent-orders .data-table')[0],
            $$('.next-page')[0],
            '.recent-orders .data-table tbody > tr'
        )
    }
    
    //-----------------------------//

    $$('label[for]').each(function(label) {
        label.observe('click', function() {});
    });
    
    $$('input.validate-email').each(function (input) {
        input.writeAttribute('type', 'email');
    });
    
    $$('.form-list img[src*="calendar.gif"]').each(function (img) {
        img.up().insert({ 'top' : img });
    });

    if ( navigator.geolocation ) {
        //navigator.geolocation.getCurrentPosition(handler);
    }

    if ( $('my-reviews-table') ) {
        $('my-reviews-table').wrap('div', { 'class' : 'my-reviews-table-wrap' });
    }
    
    $$('.my-account .dashboard .box-title').each(function (elem) {
        elem.observe('click', function (e) {
            if ( e.target.hasClassName('box-title') ) {
                this.toggleClassName('collapsed').next().toggle();
            }
        }).next().hide();
    });

    var transformPref = Modernizr.prefixed('transform');

    function supportsTouchCallout () {
        var div = document.createElement('div'),
            supports = div.style['webkitTouchCallout'] !== undefined || div.style['touchCallout'] !== undefined;

        return supports
    }

    $$('input[name=qty], input[name*=super_group], input[name*=qty]').each(function (el) {
        var defaultValue = el.value;
        el.observe('focus', function () {
            if (this.value == defaultValue) this.value = '';
        });
        el.observe('blur', function () {
            if (this.value == "") this.value = defaultValue;
        });
    });

    if ( $('product-review-table') ) {
        $('product-review-table').wrap('div', {'class' : 'review-table-wrap'}).on('click', 'input[type="radio"]', function (e) {

            $this = e.target;

            $this.up('tr').select('td').invoke('removeClassName', 'checked');

            $this.up().previousSiblings().each(function (td) {
                if ( td.hasClassName('value') ) {
                    td.addClassName('checked');
                }
            });

        });
    }

    function is_touch_device() {
      try {
        document.createEvent("TouchEvent");
        return true;
      } catch (e) {
        return false;
      }
    }

    var touch = is_touch_device();

    $$('select[multiple]').each(function (select) {
        var select_options = new Element('ol', {'class': 'select-multiple-options'}).wrap('div', { 'class' : 'select-multiple-options-wrap' }),
            selected;

        select.wrap('div', { 'class': 'select-multiple-wrap' });
        select.select('option').each(function(option) {
            select_options.down().insert({ bottom : new Element('li', { 'class' :  'select-option', 'data-option-value' : option.value }).update(option.text) });
        });

        select_options.insert({ top : new Element('div', { 'class' : 'select-heading' }).update('Choose options...').insert({ top : new Element('span', { 'class' : 'select-close' }).update('×') }) });

        var closeSelect = function() {
            select_options.setStyle({ 'visibility' : 'hidden' });
            selected = [];
            select.select('option').each(function (option) {
                if (option.selected) {
                    selected.push(option.text)
                }
            });

            if (selected.size() > 0) {
                select.previous().update('<span class="selected-counter"></span>' + selected.join(', ')).addClassName('filled');
                select.previous().select('span')[0].update(selected.size());
            } else {
                select.previous().update('Choose options...').removeClassName('filled');
            }
            document.stopObserving('click', closeSelect);
        }

        select_options.select('.select-close')[0].observe('click', closeSelect );

        select_options.on('click', '.select-option', function(e, elem) {
            var option = select.select('option[value=' + elem.readAttribute('data-option-value') + ']')[0];
            elem.toggleClassName('active');
            if (option.selected) {
                option.selected = false
            } else {
                option.selected = true;
            }
            if (typeof bundle !== 'undefined') bundle.changeSelection(select);
        });

        select.insert({ before : select_options });
        select.insert({
            before: new Element('div', {'class': 'select-multiple'}).update("Choose options...").observe('click', function(e) {
                    select.previous('.select-multiple-options-wrap').setStyle({ 'visibility' : 'visible' }).observe('click', function(e) {
                        e.stopPropagation();
                    });
                    setTimeout(function() {
                        document.observe('click', closeSelect)
                    }, 1);
                })
        });
        select.setStyle({ 'visibility' : 'hidden', 'position' : 'absolute' });
    });

    var supportsOrientationChange = "onorientationchange" in window,
    orientationEvent = supportsOrientationChange ? "orientationchange" : "resize";

    Event.observe(window, orientationEvent, function() {
        var orientation, page, transformValue = {};

        switch(window.orientation){
            case 0:
            orientation = "portrait";
            break;

            case -90:
            orientation = "landscape";
            break;

            case 90:
            orientation = "landscape";
            break;
        }

        if ( $('nav-container') ) {

            setTimeout(function () {
                $$("#nav-container ul").each(function(ul) {
                    ul.setStyle({'width' : document.body.offsetWidth + "px"});
                });

                page = Math.floor(Math.abs(sliderPosition/viewportWidth));
                sliderPosition = (sliderPosition + viewportWidth*page) - document.body.offsetWidth*page;
                viewportWidth = document.body.offsetWidth;

                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = "translate3d(" + sliderPosition + "px, 0, 0)";
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = "translate(" + sliderPosition + "px, 0)";
                }
                $("nav-container").setStyle(transformValue);

                if ( upSellCarousel ) {
                    if (orientation === 'landscape') {
                        upSellCarousel.resize(3);
                    } else {
                        upSellCarousel.resize(2);
                    }
                }
            }, 400);

        }

    });

    //alert(Modernizr.prefixed('transform'));

    // Home Page Slider

    //alert(transformPref);
    var sliderPosition = 0,
        viewportWidth = document.body.offsetWidth,
        last,
        diff;

    $$("#nav-container ul").each(function(ul) { ul.style.width = document.body.offsetWidth + "px"; });

    $$("#nav a").each(function(sliderLink) {
        if (sliderLink.next(0) !== undefined) {
            sliderLink.clonedSubmenuList = sliderLink.next(0);

            sliderLink.observe('click', function(e) {

                e.preventDefault();
                var transformValue = {}

                //homeLink.hasClassName('disabled') ? homeLink.removeClassName('disabled') : '';

                if (last) {
                    diff = e.timeStamp - last
                }
                last = e.timeStamp;
                if (diff && diff < 200) {
                    return
                }
                if (!this.clonedSubmenuList.firstDescendant().hasClassName('subcategory-header')) {
                    var subcategoryHeader = new Element('li', {'class': 'subcategory-header'});
                    subcategoryHeader.insert({
                        top: new Element('button', {'class': 'previous-category'}).update("Back").wrap('div', {'class':'button-wrap'}),
                        bottom: this.innerHTML
                    });
                    this.clonedSubmenuList.insert({
                        top: subcategoryHeader
                    });
                    subcategoryHeader.insert({ after : new Element('li').update('<a href="' + sliderLink.href + '"><span>All Products</span></a>') });

                    this.clonedSubmenuList.firstDescendant().firstDescendant().observe('click', function(e) {
                        if (last) {
                            diff = e.timeStamp - last
                        }
                        last = e.timeStamp;
                        if (diff && diff < 200) {
                            return
                        }
                        if ( Modernizr.csstransforms3d ) {
                            transformValue[transformPref] = "translate3d(" + (document.body.offsetWidth + sliderPosition) + "px, 0, 0)";
                        } else if ( Modernizr.csstransforms ) {
                            transformValue[transformPref] = "translate(" + (document.body.offsetWidth + sliderPosition) + "px, 0)";
                        }
                        $("nav-container").setStyle(transformValue);
                        sliderPosition = sliderPosition + document.body.offsetWidth;
                        setTimeout(function() { $$("#nav-container > ul:last-child")[0].remove(); $("nav-container").setStyle({'height' : 'auto'})  }, 250)
                    });
                    new NoClickDelay(this.clonedSubmenuList);
                };

                $("nav-container").insert(this.clonedSubmenuList.setStyle({'width' : document.body.offsetWidth + 'px'}));
                $('nav-container').setStyle({'height' : this.clonedSubmenuList.getHeight() + 'px'});

                if ( Modernizr.csstransforms3d ) {

                    transformValue[transformPref] = "translate3d(" + (sliderPosition - document.body.offsetWidth) + "px, 0, 0)";

                } else if ( Modernizr.csstransforms ) {

                    transformValue[transformPref] = "translate(" + (sliderPosition - document.body.offsetWidth) + "px, 0)";

                }

                $("nav-container").setStyle(transformValue);

                sliderPosition = sliderPosition - document.body.offsetWidth;
            });
        };
    });

    function getSupportedProp(proparray){
        var root = document.documentElement;
        for ( var i = 0; i < proparray.length; i++ ) {
            if ( typeof root.style[proparray[i]] === "string") {
                return proparray[i];
            }
        }
    }

    function NoClickDelay(el) {
        if ( getSupportedProp(['OTransform']) ) {
            return
        }
        this.element = typeof el == 'object' ? el : document.getElementById(el);
        if( window.Touch ) this.element.addEventListener('touchstart', this, false);
    }

    NoClickDelay.prototype = {
        handleEvent: function(e) {
            switch(e.type) {
                case 'touchstart': this.onTouchStart(e); break;
                case 'touchmove': this.onTouchMove(e); break;
                case 'touchend': this.onTouchEnd(e); break;
            }
        },

        onTouchStart: function(e) {
            this.moved = false;

            this.theTarget = document.elementFromPoint(e.targetTouches[0].clientX, e.targetTouches[0].clientY);
            if(this.theTarget.nodeType == 3) this.theTarget = theTarget.parentNode;
            this.theTarget.className+= ' pressed';

            this.element.addEventListener('touchmove', this, false);
            this.element.addEventListener('touchend', this, false);
        },

        onTouchMove: function() {
            this.moved = true;
            this.theTarget.className = this.theTarget.className.replace(/ ?pressed/gi, '');
        },

        onTouchEnd: function(e) {
            e.preventDefault();

            this.element.removeEventListener('touchmove', this, false);
            this.element.removeEventListener('touchend', this, false);

            if( !this.moved && this.theTarget ) {
                this.theTarget.className = this.theTarget.className.replace(/ ?pressed/gi, '');
                var theEvent = document.createEvent('MouseEvents');
                theEvent.initEvent('click', true, true);
                this.theTarget.dispatchEvent(theEvent);
            }

            this.theTarget = undefined;
        }
    };

    if (document.getElementById('nav')) {
        new NoClickDelay(document.getElementById('nav'));
    }

    //iPhone header menu

    $$('dt.menu a')[0].observe('click', function(e) {
            var parent = this.up(), transformValue = {};
            if (parent.hasClassName('active')) {
                parent.removeClassName('active');

                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = 'translate3d(0, -100%, -1px)';
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate3d(0, -100%)';
                    transformValue['visibility']  = 'hidden';
                }

                $$('.menu-box')[0].setStyle(transformValue);

            } else {

                this.removeClassName('active');

                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = 'translate3d(0, -100%, -1px)';
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate3d(0, -100%)';
                    transformValue['visibility']  = 'hidden';
                }

                $$('.menu-box')[0].setStyle(transformValue);

                parent.addClassName('active');

                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = 'translate3d(0, 0%, -1px)';
                    transformValue['visibility']  = 'visible';
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate3d(0, 0%)';
                    transformValue['visibility']  = 'visible';
                }
                parent.next().setStyle(transformValue);
            };
            e.preventDefault();
        });

    if ( $('menu') ) {
        $('menu').select('dd').each(function (elem) {
            elem.observe('webkitTransitionEnd', function (e) {
                if ( !elem.previous().hasClassName('active') ) {
                    elem.setStyle({'visibility' : 'hidden'});
                } else {
                    elem.setStyle({'top' : '1px'});
                }
            });
        });
    }

    //iPhone header menu switchers
    if( $$('#language-switcher li.selected a')[0] ) {
        var curLang = $$('#language-switcher li.selected a')[0].innerHTML;
        $('current-language').update(curLang);

        $$('#language-switcher > a')[0].observe('click', function (e) {
            if ( !this.next().visible() )
                $$('.switcher-options').invoke('hide');
            this.next().toggle().toggleClassName('visible');
            e.preventDefault();
        });
    }

    if( $$('#store-switcher li.selected a')[0] ) {
        var curStore = $$('#store-switcher li.selected a')[0].innerHTML;
        $('current-store').update(curStore);

        $$('#store-switcher > a')[0].observe('click', function (e) {
            if ( !ithis.next().visible() )
                $$('.switcher-options').invoke('hide');
            this.next().toggle().toggleClassName('visible');
            e.preventDefault();
        });
     }

    //Slider

    var Carousel = Class.create({
       initialize: function (carousel, itemsContainer, options) {
           this.options  = Object.extend({
              visibleElements: 3,
              threshold: {
                  x: 30,
                  y: 40
              },
              preventDefaultEvents: false
           }, options || {});

           this.carousel = carousel;
           this.items    = itemsContainer.addClassName('carousel-items');
           this.itemsWrap = this.items.wrap('div', {'class' : 'carousel-items-wrap'});
           this.itemsLength = this.items.childElements().size();
           this.counter  = this.carousel.insert(new Element('div', {'class' : 'counter'})).select('.counter')[0];
           this.controls = carousel.select('.controls')[0] || this.carousel.insert({ top: new Element('div', { 'class' : 'controls'}) }).select('.controls')[0];
           this.prevButton = carousel.select('.prev')[0] || this.controls.insert({ top: new Element('span', { 'class' : 'prev'}) }).select('.prev')[0].addClassName('disabled');
           this.nextButton = carousel.select('.next')[0] || this.controls.insert({ top: new Element('span', { 'class' : 'next'}) }).select('.next')[0];
           this.originalCoord = { x: 0, y: 0 };
           this.finalCoord    = { x: 0, y: 0 };

           this.carousel.wrap('div', { 'class' : 'carousel-wrap' });

           this.nextButton.observe('click', this.moveRight.bind(this));
           this.prevButton.observe('click', this.moveLeft.bind(this));
           this.itemsWrap.observe('touchstart', this.touchStart.bind(this));
           this.itemsWrap.observe('touchmove', this.touchMove.bind(this));
           this.itemsWrap.observe('touchend', this.touchEnd.bind(this));
       },
       init: function () {
           this.itemPos  = 0;
           this.lastItemPos = (this.itemsLength-this.options.visibleElements) * 100/this.options.visibleElements;
           this.itemWidth = 100/this.options.visibleElements + '%';
           this.screens  = Math.ceil(this.itemsLength/this.options.visibleElements);

           this.resizeChilds();
           this.drawCounter();

           return this;
        },
        resize: function(visibleElements) {
            var transformValue = {};
            this.options.visibleElements = visibleElements;
            this.counter.childElements().invoke('remove');
            if ( Modernizr.csstransforms3d ) {
                transformValue[transformPref] = 'translateX(' + 0 + '%)';
            } else if ( Modernizr.csstransforms ) {
                transformValue[transformPref] = 'translate(' + 0 + '%, 0)';
            }
            this.items.setStyle(transformValue);
            this.prevButton.addClassName('disabled');
            this.nextButton.removeClassName('disabled');
            this.init();
        },
        resizeChilds: function () {
           this.items.childElements().each( function(n) {
              n.setStyle({
                  'width': this.itemWidth
              });
           }, this);
        },
        drawCounter: function () {
            if (this.screens > 1) {
                 if (this.controls)
                     this.controls.show()
                 for (var i = 0; i < this.screens; i++) {
                   if (i === 0) {
                       this.counter.insert(new Element('span', {'class': 'active'}));
                   } else {
                       this.counter.insert(new Element('span'));
                   }
               };
           } else {
               if (this.controls)
                   this.controls.hide();
           }
        },
        moveRight: function (e) {
            if(Math.abs(this.itemPos) < this.lastItemPos) {
                var transformValue = {};
                this.itemPos -= 100/this.options.visibleElements * this.options.visibleElements;
                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = 'translateX(' + this.itemPos + '%)';
                    transformValue['position']    = 'relative';
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate(' + this.itemPos + '%, 0)';
                    transformValue['position']    = 'relative';
                }
                this.items.setStyle(transformValue);
                if (Math.abs(this.itemPos) >= this.lastItemPos) {
                    this.nextButton.addClassName('disabled');
                }

                if (this.prevButton.hasClassName('disabled')) {
                    this.prevButton.removeClassName('disabled');
                };
                this.counter.select('.active')[0].removeClassName('active').next().addClassName('active');
            }
        },
        moveLeft: function (e) {
            if (this.itemPos !== 0) {
                var transformValue = {};
                this.itemPos += 100/this.options.visibleElements * this.options.visibleElements;
                if ( Modernizr.csstransforms3d ) {
                    transformValue[transformPref] = 'translateX(' + this.itemPos + '%)';
                    transformValue['position']    = 'relative';
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate(' + this.itemPos + '%, 0)';
                    transformValue['position']    = 'relative';
                }
                this.items.setStyle(transformValue);

                if(this.itemPos === 0) {
                    this.prevButton.addClassName('disabled');
                };

                if (this.nextButton.hasClassName('disabled')) {
                    this.nextButton.removeClassName('disabled');
                };
                this.counter.select('.active')[0].removeClassName('active').previous().addClassName('active');
            }
        },
        touchStart: function (e) {
            this.originalCoord.x = event.targetTouches[0].pageX;
            this.originalCoord.y = event.targetTouches[0].pageY;
        },
        touchMove: function (e) {
            this.finalCoord.x = e.targetTouches[0].pageX;
            this.finalCoord.y = e.targetTouches[0].pageY;

            var changeX = 0;
            changeX = this.originalCoord.x - this.finalCoord.x;

            if(Math.abs(changeX) > this.options.threshold.x) {
                e.preventDefault();
            }
        },
        touchEnd: function (e) {
            if ( e.preventSwipe ) {
                return
            }
            var changeX;
            changeX = this.originalCoord.x - this.finalCoord.x;
            if(changeX > this.options.threshold.x) {
                this.moveRight(e);
            }
            if(changeX < this.options.threshold.x * -1) {
                this.moveLeft(e);
            }
        }
    });

    if ( $$('.box-up-sell')[0] ) {
        var upSellCarousel = new Carousel($$('.box-up-sell')[0], $$('.products-grid')[0], {
            visibleElements: 2,
            preventDefaultEvents: true
        }).init();
    }

    /*
    if ( $$('.product-gallery')[0] ) {
        var galleryCarousel = new Carousel($$('.product-gallery')[0], $$('.product-gallery > ul')[0], {
            visibleElements: 1,
            preventDefaultEvents: false
        }).init();
    }
    */

    if ( $$('.product-view .product-image li').size() > 1 ) {
        var productGallery = new Carousel($$('.product-view .product-image')[0], $$('.product-image ul')[0], {
            visibleElements: 1,
            preventDefaults: false
        }).init();
    }

    // Swipe Functionality

    var Swipe = Class.create( Carousel, {
        initialize: function (elem, swipeLeft, swipeRight, options) {
            this.options  = Object.extend({
                threshold: {
                    x: 50,
                    y: 20
                },
                preventDefaultEvents: false
            }, options || {});

            this.elem = elem;
            this.originalCoord = { x: 0, y: 0 };
            this.finalCoord    = { x: 0, y: 0 };

            this.elem.observe('touchstart', this.touchStart.bind(this));
            this.elem.observe('touchmove', this.touchMove.bind(this));
            this.elem.observe('touchend', this.touchEnd.bind(this));
            this.moveLeft = swipeRight;
            this.moveRight = swipeLeft;
        }
    });

    /*

    var verticalSwipe = Class.create( Carousel, {
        initialize: function (elem, swipeUp, swipeDown, options) {
            this.options  = Object.extend({
                threshold: {
                    x: 10,
                    y: 10
                },
                preventDefaultEvents: false
            }, options || {});

            this.elem = elem;
            this.originalCoord = { x: 0, y: 0 };
            this.finalCoord    = { x: 0, y: 0 };

            this.elem.observe('touchstart', this.touchStart.bind(this));
            this.elem.observe('touchmove', this.touchMove.bind(this));
            this.elem.observe('touchend', this.touchEnd.bind(this));
            this.moveLeft = swipeDown;
            this.moveRight = swipeUp;
        },
        touchStart: function (e) {
            e.preventDefault();
            this.originalCoord.x = event.targetTouches[0].pageX;
            this.originalCoord.y = event.targetTouches[0].pageY;
        },
        touchMove: function (e) {
            this.finalCoord.x = e.targetTouches[0].pageX;
            this.finalCoord.y = e.targetTouches[0].pageY;
        },
        touchEnd: function (e) {
            var changeY = this.originalCoord.y - this.finalCoord.y;
            if(changeY > this.options.threshold.y) {
                this.moveRight();
            }
            if(changeY < this.options.threshold.y * -1) {
                this.moveLeft();
            }
        }
    });

    if ( $$('.block-cart')[0] ) {
        new verticalSwipe($$('dt.cart')[0],
            function () {
            },
            function () {
                $$('.block-cart')[0].setStyle({'webkitTransform':'translate3d(0, 42px, 0)'})
            }
        );
    };

    */

    zoomGallery = Class.create({
        initialize: function (gallery, options) {
            this.options  = Object.extend({
                threshold: {
                  x: 30,
                  y: 40
              }
            }, options || {});

            this.gallery = gallery;
            this.counter  = this.gallery.insert({after : new Element('div', {'class' : 'counter'})}).next();
            this.controls = gallery.select('.controls')[0] || this.gallery.insert({ bottom: new Element('div', { 'class' : 'controls'}) }).select('.controls')[0];
            this.prevButton = gallery.select('.prev')[0] || this.controls.insert({ top: new Element('span', { 'class' : 'prev'}) }).select('.prev')[0].addClassName('disabled');
            this.nextButton = gallery.select('.next')[0] || this.controls.insert({ top: new Element('span', { 'class' : 'next'}) }).select('.next')[0];
            this.wrap = this.gallery.down();
            this.scale = 1.0;
            this.dimensions;
            this.items    = gallery.select('img');
            this.itemsLength = this.items.size();
            this.pos = 0;
            this.step = (100/this.itemsLength).toFixed(2) * 1;
            this.lastPos = this.step * this.itemsLength;
            this.originalCoord = { x: 0, y: 0 };
            this.finalCoord    = { x: 0, y: 0 };
            this.offset = { x: 0, y: 0 };
            this.ret = { x: 0, y: 0 };
            
            this.nextButton.observe('click', this.moveRight.bind(this));
            this.prevButton.observe('click', this.moveLeft.bind(this));
            
            if (this.itemsLength < 2) {
                this.controls.hide();
            }

            this.items.each(function (item) {
                item.observe('touchstart', this.touchStart.bind(this));
                item.observe('touchmove', this.touchMove.bind(this));
                item.observe('touchend', this.touchEnd.bind(this));
                item.observe('gesturestart', this.gestureStart.bind(this));
                item.observe('gesturechange', this.gestureChange.bind(this));
                item.observe('gestureend', this.gestureEnd.bind(this));
            }.bind(this));

            this.wrap.setStyle({
                'width' : this.itemsLength * 100 + '%'
            });

            this.drawCounter();
        },
        drawCounter: function () {
            if (this.itemsLength > 1) {
                for (var i = 0; i < this.itemsLength; i++) {
                    if (i === 0) {
                        this.counter.insert(new Element('span', {'class': 'active'}));
                    } else {
                    this.counter.insert(new Element('span'));
                    }
                };
            }
        },
        moveRight: function (elem) {

            if (this.pos !== this.lastPos - this.step) {
            
                if(elem == event) {
                    this.items.each(function (elm) {
                        elm.setStyle({
                            'webkitTransition' : '300ms linear',
                            'webkitTransform' : 'scale3d(1, 1, 1)'
                        });
                    });
                } else {
                    elem.setStyle({
                        'webkitTransition' : '300ms linear',
                        'webkitTransform' : 'scale3d(1, 1, 1)'
                    });
                }

                this.scale = 1.0;

                this.pos += this.step;

                var transformValue = {};
                if ( Modernizr.csstransforms3d ) {
                    this.wrap.setStyle({
                        'webkitTransition' : '300ms linear',
                        'webkitTransform' : 'translate3d(' + this.pos*-1 + '%, 0, 0)'
                    });
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate(' + this.pos*-1 + '%, 0)';
                    this.wrap.setStyle(transformValue);
                }
                
                if (this.pos == this.lastPos - this.step) {
                    this.nextButton.addClassName('disabled');
                }

                if (this.prevButton.hasClassName('disabled')) {
                    this.prevButton.removeClassName('disabled');
                };

                this.counter.select('.active')[0].removeClassName('active').next().addClassName('active');

            }
        },
        moveLeft: function (elem) {

            if (this.pos !== 0) {

                if(elem == event) {
                    this.items.each(function (elm) {
                        elm.setStyle({
                            'webkitTransition' : '300ms linear',
                            'webkitTransform' : 'scale3d(1, 1, 1)'
                        });
                    });
                } else {
                    elem.setStyle({
                        'webkitTransition' : '300ms linear',
                        'webkitTransform' : 'scale3d(1, 1, 1)'
                    });
                }

                this.scale = 1.0;

                this.pos -= this.step;

                var transformValue = {};
                if ( Modernizr.csstransforms3d ) {
                    this.wrap.setStyle({
                        'webkitTransition' : '300ms linear',
                        'webkitTransform' : 'translate3d(' + this.pos*-1 + '%, 0, 0)'
                    });
                } else if ( Modernizr.csstransforms ) {
                    transformValue[transformPref] = 'translate(' + this.pos*-1 + '%, 0)';
                    this.wrap.setStyle(transformValue);
                }
                
                if (this.pos == 0) {
                    this.prevButton.addClassName('disabled');
                }

                if (this.nextButton.hasClassName('disabled')) {
                    this.nextButton.removeClassName('disabled');
                };


                this.counter.select('.active')[0].removeClassName('active').previous().addClassName('active');
            }
            //console.log('moveLeft()');
        },
        gestureStart : function (e) {
            var $this = e.target;

            e.preventDefault();

            this.gestureStart = true;
            this.dimensions = $this.getDimensions();
        },
        gestureChange : function (e) {
            e.preventDefault();
            var $this = e.target

            if ( (e.scale * this.scale) > 2 )
                return

            $this.setStyle({
                'webkitTransition' : '',
                'webkitTransform' : 'scale3d(' + (e.scale * this.scale) + ', ' + (e.scale * this.scale) + ', 1)',
            });
        },
        gestureEnd : function (e) {
            var $this = e.target;

            if ( (e.scale * this.scale) < 1 ) {
                $this.setStyle({
                    'webkitTransition' : '300ms linear',
                    'webkitTransform' : 'scale3d(1, 1, 1)'
                });
                this.scale = 1.0;
            } else if ( e.scale > 2 ) {
                this.scale = 2;
            } else {
                this.scale *= e.scale;
            }

            setTimeout(function () {
                this.gestureStart = false;
            }.bind(this), 50);

            this.originalCoord.x = this.originalCoord.y = this.finalCoord.x = this.finalCoord.y = this.offset.x = this.offset.y = 0;
        },
        touchStart: function (e) {
            var $this = e.target;

            if (e.targetTouches.length != 1) {
                return false
            }

            this.t1 = Date.now();

            this.originalCoord.x = e.targetTouches[0].clientX;
            this.originalCoord.y = e.targetTouches[0].clientY;

            $this.setStyle({ 'webkitTransition' : '' });
        },
        touchMove: function (e) {

            this.finalCoord.x = e.targetTouches[0].clientX;
            this.finalCoord.y = e.targetTouches[0].clientY;

            if (e.targetTouches.length != 1 || this.scale === 1.0 || this.gestureStart)
                return false

            e.preventDefault();

            var $this = e.target;

            var changeX = this.offset.x + this.finalCoord.x - this.originalCoord.x,
                changeY = this.offset.y + this.finalCoord.y - this.originalCoord.y,
                topX = (this.dimensions.width  * (this.scale - 1))/2,
                topY = (this.dimensions.height * (this.scale - 1))/2,
                tension = 1.55;

            if ( topX < Math.abs(changeX) ) {
                if ( changeX < 0 ) {
                    changeX = changeX - (changeX + topX)/tension;
                } else {
                    changeX = changeX - (changeX - topX)/tension;
                }
            }

            if ( topY < Math.abs(changeY) ) {
                if ( changeY < 0 ) {
                    changeY = changeY - (changeY + topY)/tension;
                } else {
                    changeY = changeY - (changeY - topY)/tension;
                }
            }

            $this.setStyle({
                'webkitTransform' : 'translate3d(' + changeX + 'px,' + changeY + 'px, 0) scale3d(' + this.scale + ',' + this.scale  + ',1)'
            });

        },
        touchEnd: function (e) {

            this.t2 = Date.now();

            var $this = e.target,
                timeDelta = this.t2 - this.t1,
                changeX = this.originalCoord.x - this.finalCoord.x,
                changeY = this.originalCoord.y - this.finalCoord.y;

            if(changeX > this.options.threshold.x && Math.abs(changeY) < 40 && timeDelta < 300) {
                this.moveRight($this);
            }
            if(changeX < this.options.threshold.x * -1 && Math.abs(changeY) < 40 && timeDelta < 300) {

                this.moveLeft($this);
            }

            if (e.targetTouches.length > 0 || this.gestureStart || timeDelta < 100)
                return false;

            this.offset.x += this.finalCoord.x - this.originalCoord.x;
            this.offset.y += this.finalCoord.y - this.originalCoord.y;

            var topX = (this.dimensions.width  * (this.scale - 1))/2,
                topY = (this.dimensions.height * (this.scale - 1))/2,
                moved = false;

            if ( Math.abs(this.offset.x) > topX ) {

                moved = true;
                $this.setStyle({
                    'webkitTransition' : '-webkit-transform 100ms ease-out',
                    'webkitTransform' : 'translate3d(' + (this.offset.x  < 0 ? topX*-1 : topX) + 'px,' + this.offset.y + 'px, 0) scale3d(' + this.scale + ',' + this.scale  + ',1)'
                });

                this.offset.x = this.offset.x  < 0 ? topX*-1 : topX;

            }

            if ( Math.abs(this.offset.y) > topY ) {
                moved = true;
                $this.setStyle({
                    'webkitTransition' : '-webkit-transform 100ms ease-out',
                    'webkitTransform' : 'translate3d(' + this.offset.x + 'px,' + (this.offset.y  < 0 ? topY*-1 : topY) + 'px, 0) scale3d(' + this.scale + ',' + this.scale  + ',1)'
                });

                this.offset.y = this.offset.y  < 0 ? topY*-1 : topY;

            }

            if ( Math.abs(this.offset.x) > topX && Math.abs(this.offset.y) > topY && !moved ) {

                $this.setStyle({
                    'webkitTransition' : '-webkit-transform 100ms ease-out',
                    'webkitTransform' : 'translate3d(' + (this.offset.x  < 0 ? topX*-1 : topX) + 'px,' + (this.offset.y  < 0 ? topY*-1 : topY) + 'px, 0) scale3d(' + this.scale + ',' + this.scale  + ',1)'
                });

                this.offset.x = this.offset.x  < 0 ? topX*-1 : topX;
                this.offset.y = this.offset.y  < 0 ? topY*-1 : topY;

            }

        },
    });

    if ( $$('.c-list') && supportsTouchCallout() ) {

        $$('.c-list .product-image').each(function(n) {
            var parent = n.up('a'),
                clone  = n.up().clone(true).addClassName('cloned');
            parent.insert(clone.wrap('div', {'class' : 'cloned-wrap'}));

            new webkit_draggable(clone.up(), { handle : clone.select('.product-image')[0], revert : true, scroll : true, onStart : function(r, e) {
                    r.setStyle({'opacity':'100'}).down('.wrap').addClassName('drop-start');
                },
                onEnd : function(r, e) {
                    r.setStyle({'opacity':'0'}).down('.wrap').removeClassName('drop-start');
                }
            });
        });
        webkit_drop.add($('menu'),
        {
            onDrop : function(elem, e) { e.preventDefault(); setLocation(elem.up('li').down('.actions li:last-child a').readAttribute('href')); elem.remove(); },
            onOver : function(elem, e) { e.preventDefault(); elem.down().addClassName('to-cart-animate'); },
            onOut  : function(elem) { elem.down().removeClassName('to-cart-animate'); }
        });

    }

    if ( $('customer-reviews') ) {
        $('customer-reviews').select('dt > a').each(function (a) {
            a.replace('<h3>' + a.innerHTML + '</h3>');
        });
    }

});
