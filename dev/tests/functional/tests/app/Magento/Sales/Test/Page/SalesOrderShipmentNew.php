<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Page;

use Mtf\Client\Element\Locator;
use Mtf\Factory\Factory;
use Mtf\Page\Page;

/**
 * Class SalesOrder
 * Manage orders page
 *
 */
class SalesOrderShipmentNew extends Page
{
    /**
     * URL for manage orders page
     */
    const MCA = 'sales/order/shipment/new';

    /**
     * Shipment totals block
     *
     * @var string
     */
    protected $totalsBlock = '.order-totals';

    /**
     * Custom constructor
     */
    protected function _init()
    {
        $this->_url = $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get shipment totals
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Shipment\Totals
     */
    public function getTotalsBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderShipmentTotals(
            $this->_browser->find($this->totalsBlock, Locator::SELECTOR_CSS)
        );
    }
}
