<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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

        $this->_formScripts[] = '
            toggleParentVis("add_review_form");
            toggleVis("save_button");
            toggleVis("reset_button");
        ';

        $this->_formInitScripts[] = '
            //<![CDATA[
            var review = function() {
                return {
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
                        var con = new Ext.lib.Ajax.request(\'POST\', review.productInfoUrl, {success:review.reqSuccess,failure:review.reqFailure}, {form_key:FORM_KEY});
                    },

                    showForm : function() {
                        toggleParentVis("add_review_form");
                        toggleVis("productGrid");
                        toggleVis("save_button");
                        toggleVis("reset_button");
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

                    reqSuccess :function(o) {
                        var response = Ext.util.JSON.decode(o.responseText);
                        if( response.error ) {
                            alert(response.message);
                        } else if( response.id ){
                            $("product_id").value = response.id;

                            $("product_name").innerHTML = \'<a href="' .
            $this->getUrl(
                'catalog/product/edit'
            ) .
            'id/\' + response.id + \'" target="_blank">\' + response.name + \'</a>\';
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
           //]]>
        ';
    }

    /**
     * Get add new review header text
     *
     * @return string
     */
    public function getHeaderText()
    {
        return __('Add New Review');
    }
}
