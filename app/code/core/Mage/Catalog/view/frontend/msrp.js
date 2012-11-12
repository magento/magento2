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
 * @package     base_default
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!window.Catalog) {
    window.Catalog = {};
}

Catalog.Map = {

    helpLinks: [],

    active: false

//    addHelpLink: function(linkElement, title, actualPrice, msrpPrice, addToCartLink) {
//        if (typeof linkElement == 'string') {
//            linkElement = $$(linkElement)[0];
//        }
//
//        if (!linkElement) {
//            return;
//        }
//
//        var helpLink = {
//            'link': linkElement
//        };
//
//        var showPopup = false;
//
//        if (typeof title == 'string' && title) {
//            helpLink.title = title;
//            showPopup = true;
//        }
//
//        if (typeof actualPrice == 'string' && actualPrice || typeof actualPrice == 'object' && actualPrice) {
//            helpLink.price = actualPrice;
//            showPopup = true;
//        }
//
//        if (typeof msrpPrice == 'string' && msrpPrice) {
//            helpLink.msrp = msrpPrice;
//            showPopup = true;
//        }
//
//        if (typeof addToCartLink == 'string' && addToCartLink) {
//            helpLink.cartLink = addToCartLink;
//        } else if (addToCartLink && addToCartLink.url) {
//            helpLink.cartLink = addToCartLink.url;
//            if (addToCartLink.qty) {
//                helpLink.qty = addToCartLink.qty;
//            }
//            if (addToCartLink.notUseForm) {
//                helpLink.notUseForm = addToCartLink.notUseForm;
//            }
//        }
//
//        if (!showPopup) {
//            this.setGotoView(linkElement, addToCartLink);
//        } else {
//            var helpLinkIndex = this.helpLinks.push(helpLink) - 1;
//            Event.observe(linkElement, 'click', this.showHelp.bind(this.helpLinks[helpLinkIndex]));
//        }
//        return helpLink;
//    },

//    setGotoView: function(element, viewPageUrl) {
//        $(element).stopObserving('click');
//        element.href = viewPageUrl;
//        if(window.opener) {
//            Event.observe(element, 'click', function(event) {
//                setPLocation(this.href,true);
//                Catalog.Map.hideHelp();
//                event.stop();
//            });
//        } else {
//            Event.observe(element, 'click', function(event) {
//                setLocation(this.href);
//                Catalog.Map.hideHelp();
//                window.opener.focus();
//                event.stop();
//            });
//        }
//    },

//    showSelects: function() {
//        var elements = document.getElementsByTagName("select");
//        for (i=0;i< elements.length;i++) {
//            elements[i].style.visibility='visible';
//        }
//    },

//    hideSelects: function() {
//        var elements = document.getElementsByTagName("select");
//        for (i=0;i< elements.length;i++) {
//            elements[i].style.visibility='hidden';
//        }
//    },

//    showHelp: function(event) {
//        var helpBox = $('map-popup'),
//            isIE6 = typeof document.body.style.maxHeight === "undefined";
//        if (!helpBox) {
//            return;
//        }
//
//        //Move help box to be right in body tag
//        var bodyNode = $$('body')[0];
//        if (helpBox.parentNode != bodyNode) {
//            helpBox.remove();
//            bodyNode.insert(helpBox);
//			// Fix for FF4-FF5 bug with missing alt text after DOM manipulations
//			var paypalImg = helpBox.select('.paypal-logo > a > img')[0];
//			if (paypalImg) paypalImg.src = paypalImg.src;
//        }
//
//        if (this != Catalog.Map && Catalog.Map.active != this.link) {
//            helpBox.style.display = 'none';
//            if (!helpBox.offsetPosition) {
//                helpBox.offsetPosition = {left:0, top: 0};
//            }
//
//            helpBox.removeClassName('map-popup-right');
//            helpBox.removeClassName('map-popup-left');
//            if (Element.getWidth(bodyNode) < event.pageX + Element.getWidth(helpBox)) {
//                helpBox.addClassName('map-popup-left');
//            } else if (event.pageX - Element.getWidth(helpBox) < 0) {
//                helpBox.addClassName('map-popup-right');
//            }
//
//            helpBox.style.left = event.pageX - (Element.getWidth(helpBox) / 2) + 'px';
//            helpBox.style.top = event.pageY + 10 + 'px';
//
//            //Title
//            var mapTitle = $('map-popup-heading');
//            if (typeof this.title != 'undefined') {
//                Element.update(mapTitle, this.title);
//                $(mapTitle).show();
//            } else {
//                $(mapTitle).hide();
//            }
//
//            //MSRP price
//            var mapMsrp = $('map-popup-msrp-box');
//            if (typeof this.msrp != 'undefined') {
//                Element.update($('map-popup-msrp'), this.msrp);
//                $(mapMsrp).show();
//            } else {
//                $(mapMsrp).hide();
//            }
//
//            //Actual price
//            var mapPrice = $('map-popup-price-box');
//            if (typeof this.price != 'undefined') {
//                var price = typeof this.price == 'object' ? this.price.innerHTML : this.price;
//                Element.update($('map-popup-price'), price);
//                $(mapPrice).show();
//            } else {
//                $(mapPrice).hide();
//            }

//            //`Add to cart` button
//            var cartButton = $('map-popup-button');
//            if (typeof this.cartLink != 'undefined') {
//                if (typeof productAddToCartForm == 'undefined' || this.notUseForm) {
//                    Catalog.Map.setGotoView(cartButton, this.cartLink);
//                    productAddToCartForm = $('product_addtocart_form_from_popup');
//                } else {
//                    if (this.qty) {
//                        productAddToCartForm.qty = this.qty;
//                    }
//                    cartButton.stopObserving('click');
//                    cartButton.href = this.cartLink;
//                    Event.observe(cartButton, 'click', function(event) {
//                        productAddToCartForm.action = this.href;
//                        productAddToCartForm.submit(this);
//                    });
//                }
//                productAddToCartForm.action = this.cartLink;
//                var productField = $('map-popup-product-id');
//                productField.value = this.product_id;
//                $(cartButton).show();
//                $$('.additional-addtocart-box').invoke('show');
//            } else {
//                $(cartButton).hide();
//                $$('.additional-addtocart-box').invoke('hide');
//            }

//            //Horizontal line
//            var mapText = $('map-popup-text'),
//                mapTextWhatThis = $('map-popup-text-what-this'),
//                mapContent = $('map-popup-content');
//            if (!mapMsrp.visible() && !mapPrice.visible() && !cartButton.visible()) {
//                //If just `What's this?` link
//                $(mapText).hide();
//                $(mapTextWhatThis).show();
//                $(mapTextWhatThis).removeClassName('map-popup-only-text');
//                $(mapContent).hide().setStyle({visibility: 'hidden'});
//                $('product_addtocart_form_from_popup').hide();
//            } else {
//                $(mapTextWhatThis).hide();
//                $(mapText).show();
//                $(mapText).addClassName('map-popup-only-text');
//                $(mapContent).show().setStyle({visibility: 'visible'});
//                $('product_addtocart_form_from_popup').show();
//            }
//
//            $(helpBox).show();
//            if (isIE6) {
//                Catalog.Map.hideSelects();
//            }
//            var closeButton = $('map-popup-close');
//            if (closeButton) {
//                $(closeButton).stopObserving('click');
//                Event.observe(closeButton, 'click', Catalog.Map.showHelp.bind(this));
//                Catalog.Map.active = this.link;
//            }
//        } else {
//            $(helpBox).hide();
//            if (isIE6) {
//                Catalog.Map.showSelects();
//            }
//            Catalog.Map.active = false;
//        }
//
//        Event.stop(event);
//    }

//    hideHelp: function(){
//        var helpBox = $('map-popup');
//        if (helpBox) {
//            var isIE6 = typeof document.body.style.maxHeight === "undefined";
//            $(helpBox).hide();
//            if (isIE6) {
//                Catalog.Map.showSelects();
//            }
//            Catalog.Map.active = false;
//        }
//    }

//    bindProductForm: function(){
//        if (('undefined' != typeof productAddToCartForm) && productAddToCartForm) {
//            productAddToCartFormOld = productAddToCartForm;
//            productAddToCartForm = new VarienForm('product_addtocart_form_from_popup');
//            productAddToCartForm.submitLight = productAddToCartFormOld.submitLight;
//        } else if(!$('product_addtocart_form_from_popup')) {
//            return false;
//        } else if ('undefined' == typeof productAddToCartForm) {
//            productAddToCartForm = new VarienForm('product_addtocart_form_from_popup');
//        }
//
//        productAddToCartForm.submit = function(button, url) {
//            if (('undefined' != typeof productAddToCartFormOld) && productAddToCartFormOld) {
//                if (Catalog.Map.active) {
//                    Catalog.Map.hideHelp();
//                }
//                if (productAddToCartForm.qty && $('qty')) {
//                    $('qty').value = productAddToCartForm.qty;
//                }
//                parentResult = productAddToCartFormOld.submit();
//                return false;
//            }
//            if(window.opener) {
//                var parentButton = button;
//                new Ajax.Request(this.form.action, {
//                    parameters: {isAjax: 1, method: 'GET'},
//                    onSuccess: function(transport) {
//                        window.opener.focus();
//                        if (parentButton && parentButton.href) {
//                            setPLocation(parentButton.href, true);
//                            Catalog.Map.hideHelp();
//                        }
//                    }
//                });
//                return;
//            }
//            if (this.validator.validate()) {
//                var form = this.form;
//                var oldUrl = form.action;
//
//                if (url) {
//                   form.action = url;
//                }
//                if (!form.getAttribute('action')) {
//                   form.action = productAddToCartForm.action;
//                }
//                try {
//                    this.form.submit();
//                } catch (e) {
//                    this.form.action = oldUrl;
//                    throw e;
//                }
//                this.form.action = oldUrl;
//
//                if (button && button != 'undefined') {
//                    button.disabled = true;
//                }
//            }
//        };
//    }
};

Event.observe(window, 'resize', function (event) {
    if (Catalog.Map.active) {
        Catalog.Map.showHelp(event);
    }
})

$(document).observe('bundle:reload-price', function (event) { //reload price
    var data = event.memo, bundle = data.bundle;
    if (!Number(bundle.config.isMAPAppliedDirectly) && !Number(bundle.config.isFixedPrice)) {
        var canApplyMAP = false;
        try {
            for (var option in bundle.config.selected) {
                if (bundle.config.options[option] && bundle.config.options[option].selections) {
                    var selections = bundle.config.options[option].selections;
                    for (var i = 0, l = bundle.config.selected[option].length; i < l; i++) {
                        var selectionId = bundle.config.selected[option][i];
                        if (Number(selections[selectionId].canApplyMAP)) {
                            canApplyMAP = true;
                            break;
                        }
                    }
                }
                if (canApplyMAP) {
                    break;
                }
            }
        } catch (e) {
            canApplyMAP = true;
        }
        if (canApplyMAP) {
            $$('.full-product-price').each(function (e) {
                $(e).hide();
            });
            $$('.map-info').each(function (e) {
                $(e).show();
            });
            event.noReloadPrice = true;
        } else {
            $$('.full-product-price').each(function (e) {
                $(e).show();
            });
            $$('.map-info').each(function (e) {
                $(e).hide();
            });
        }
    }
});
