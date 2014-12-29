<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View;

use Magento\Backend\Test\Block\Widget\FormTabs;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Info as OrderInformationBlock;

/**
 * Class OrderForm
 * Order view tabs
 */
class OrderForm extends FormTabs
{
    /**
     * Order information block
     *
     * @var string
     */
    protected $orderInfoBlock = '[data-ui-id="sales-order-tabs-tab-content-order-info"]';

    /**
     * Get order information block
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
