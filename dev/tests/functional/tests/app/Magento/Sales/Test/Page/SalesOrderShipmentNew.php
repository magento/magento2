<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Page;

use Magento\Mtf\Client\Locator;
use Magento\Mtf\Factory\Factory;
use Magento\Mtf\Page\Page;

/**
 * Manage orders page.
 */
class SalesOrderShipmentNew extends Page
{
    /**
     * URL for manage orders page.
     */
    const MCA = 'sales/order/shipment/new';

    /**
     * Shipment totals block.
     *
     * @var string
     */
    protected $totalsBlock = '.order-totals';

    /**
     * Init page. Set page url.
     *
     * @return void
     */
    protected function initUrl()
    {
        $this->url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get shipment totals.
     *
     * @return \Magento\Sales\Test\Block\Adminhtml\Order\Shipment\Totals
     */
    public function getTotalsBlock()
    {
        return Factory::getBlockFactory()->getMagentoSalesAdminhtmlOrderShipmentTotals(
            $this->browser->find($this->totalsBlock, Locator::SELECTOR_CSS)
        );
    }
}
