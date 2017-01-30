<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info as OrderInformationBlock;

/**
 * Order view tabs
 */
class OrderForm extends FormTabs
{
    /**
     * Order information block.
     *
     * @var string
     */
    protected $orderInfoBlock = '[data-ui-id="sales-order-tabs-tab-content-order-info"]';

    /**
     * Get order information block.
     *
     * @return OrderInformationBlock
     */
    public function getOrderInfoBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info',
            ['element' => $this->_rootElement->find($this->orderInfoBlock)]
        );
    }
}
