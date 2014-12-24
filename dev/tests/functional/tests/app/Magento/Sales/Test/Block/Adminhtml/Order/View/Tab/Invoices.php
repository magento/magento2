<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab;

use Magento\Backend\Test\Block\Widget\Tab;
use Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid;

/**
 * Class Invoices
 * Invoices tab
 */
class Invoices extends Tab
{
    /**
     * Grid block css selector
     *
     * @var string
     */
    protected $grid = '#order_invoices';

    /**
     * Get grid block
     *
     * @return Grid
     */
    public function getGridBlock()
    {
        return $this->blockFactory->create(
            'Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Invoices\Grid',
            ['element' => $this->_rootElement->find($this->grid)]
        );
    }
}
