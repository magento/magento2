<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */

namespace Magento\Review\Block\Adminhtml;

/**
 * Adminhtml add Review main block
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
        $this->buttonList->update('save', 'style', 'display: none;');
        $this->buttonList->update('reset', 'id', 'reset_button');
        $this->buttonList->update('reset', 'style', 'display: none;');
        $this->buttonList->update('reset', 'onclick', 'window.review.formReset()');
        // @codingStandardsIgnoreStart
        $this->_formInitScripts[] = '
            require(["Magento_Review/js/new-review"], function (review) {
                review.configure({
                    ratingItemsUrl: "' . $this->escapeJs($this->getUrl('review/product/ratingItems')) . '",
                    productEditUrl: "' . $this->escapeJs($this->getUrl('catalog/product/edit')) . '"
                });
            });
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
