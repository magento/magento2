<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create;

/**
 * Adminhtml sales order create select store block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Store extends \Magento\Sales\Block\Adminhtml\Order\Create\AbstractCreate
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
        $this->setId('sales_order_create_store');
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Please select a store');
    }
}
