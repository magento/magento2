<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\Create\Sidebar;

/**
 * Adminhtml sales order create sidebar viewed block
 *
 * @api
 * @author      Magento Core Team <core@magentocommerce.com>
 * @since 2.0.0
 */
class Viewed extends \Magento\Sales\Block\Adminhtml\Order\Create\Sidebar\AbstractSidebar
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
        $this->setId('sales_order_create_sidebar_viewed');
        $this->setDataId('viewed');
    }

    /**
     * Retrieve display block availability
     *
     * @return false
     * @since 2.0.0
     */
    public function canDisplay()
    {
        return false;
    }

    /**
     * Retrieve availability removing items in block
     *
     * @return false
     * @since 2.0.0
     */
    public function canRemoveItems()
    {
        return false;
    }

    /**
     * Get header text
     *
     * @return \Magento\Framework\Phrase
     * @since 2.0.0
     */
    public function getHeaderText()
    {
        return __('Recently Viewed');
    }
}
