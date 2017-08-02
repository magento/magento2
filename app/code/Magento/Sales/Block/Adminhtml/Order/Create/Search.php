<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Adminhtml sales order create search block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Search extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
{
    /**
     * Constructor
     *
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('sales_order_create_search');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Please select products');
    }

    /**
     * Get buttons html
     *
     * @return string
     * @since 2.0.0
     */
    public function getButtonsHtml()
    {
        $addButtonData = [
            'label' => __('Add Selected Product(s) to Order'),
            'onclick' => 'order.productGridAddSelected()',
            'class' => 'action-add action-secondary',
        ];
        return $this->getLayout()->createBlock(
            \Magento\Backend\Block\Widget\Button::class
        )->setData(
            $addButtonData
        )->toHtml();
    }

    /**
     * Get header css class
     *
     * @return string
     * @since 2.0.0
     */
    public function getHeaderCssClass()
    {
        return 'head-catalog-product';
    }
}
