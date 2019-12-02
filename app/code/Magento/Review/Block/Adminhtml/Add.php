<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Review\Block\Adminhtml;

/**
 * Adminhtml add Review main block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Add extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Initialize add review
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_blockGroup = 'Magento_Review';
        $this->_controller = 'adminhtml';
        $this->_mode = 'add';
        $this->buttonList->update('save', 'label', __('Save Review'));
        $this->buttonList->update('save', 'id', 'save_button');
        $this->buttonList->update('reset', 'id', 'reset_button');
        $this->buttonList->update('reset', 'onclick', 'window.review.formReset()');
        $this->_formScripts[] = '
            require(["prototype"], function(){
                toggleParentVis("add_review_form");
                toggleVis("save_button");
                toggleVis("reset_button");
            });
        ';
        // @codingStandardsIgnoreStart
        $this->_formInitScripts[] = '
            require(["jquery","Magento_Review/js/rating","prototype"], function(jQuery, rating){
            window.review = function() {
                return {
                    reviewFormEditSelector: "#edit_form",
                    ratingSelector: "[data-widget=ratingControl]",
                    productInfoUrl : null,
                    formHidden : true,
                    gridRowClick : function(data, click) {
                        if(Event.findElement(click,\'TR\').title){
                            review.productInfoUrl = Event.findElement(click,\'TR\').title;
                            review.loadProductData();
                            review.showForm();
                            review.formHidden = false;
                        }
                    },
                    loadProductData : function() {
                        jQuery.ajax({
                            type: "GET",
                            url: review.productInfoUrl,
                            data: {
                                form_key: FORM_KEY
                            },
                            showLoader: true,
                            success: review.reqSuccess,
                            error: review.reqFailure
                        });
                    },
                    showForm : function() {
                        toggleParentVis("add_review_form");
                        toggleVis("productGrid");
                        toggleVis("save_button");
                        toggleVis("reset_button");
                    },
                    formReset: function() {
                        jQuery(review.reviewFormEditSelector).trigger(\'reset\');
                        jQuery(review.ratingSelector).ratingControl(\'removeRating\');
                    },
                    updateRating: function() {
                        elements = [$("select_stores"), $("rating_detail").getElementsBySelector("input[type=\'radio\']")].flatten();
                        $(\'save_button\').disabled = true;
                        var params = Form.serializeElements(elements);
                        if (!params.isAjax) {
                            params.isAjax = "true";
                        }
                        if (!params.form_key) {
                            params.form_key = FORM_KEY;
                        }
                        new Ajax.Updater("rating_detail", "' .
            $this->getUrl(
                'review/product/ratingItems'
            ) .
            '", {parameters:params, evalScripts: true,  onComplete:function(){ $(\'save_button\').disabled = false; } });
                    },

                    reqSuccess :function(response) {
                        if( response.error ) {
                            alert(response.message);
                        } else if( response.id ){
                            var productName = response.name;
                            $("product_id").value = response.id;

                            $("product_name").innerHTML = \'<a href="' .
            $this->getUrl(
                'catalog/product/edit'
            ) .
            'id/\' + response.id + \'" target="_blank">\' + productName.escapeHTML() + \'</a>\';
                        } else if ( response.message ) {
                            alert(response.message);
                        }
                    }
                }
            }();
            Event.observe(window, \'load\', function(){
                 if ($("select_stores")) {
                     Event.observe($("select_stores"), \'change\', review.updateRating);
                 }
            });
            });
           //]]>
        ';
        // @codingStandardsIgnoreEnd
    }

    /**
     * Get add new review header text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return __('New Review');
    }
}
