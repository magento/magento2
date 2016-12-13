<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\View\Tab\Shipments;

use Magento\Mtf\Client\Locator;

/**
 * Class Grid
 * Shipments grid on order view page
 */
class Grid extends \Magento\Backend\Test\Block\Widget\Grid
{
    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = '[data-column="real_shipment_id"]';

    /**
     * Css selector for shipment ids.
     *
     * @var string
     */
    protected $shipmentId = 'tbody td:nth-child(2)';

    /**
     * Shipments data grid loader Xpath locator.
     *
     * @var string
     */
    protected $loader = '//div[contains(@data-component, "sales_order_view_shipment_grid")]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="real_shipment_id"]',
        ],
        'qty_from' => [
            'selector' => '[name="total_qty[from]"]',
        ],
        'qty_to' => [
            'selector' => '[name="total_qty[to]"]',
        ],
    ];

    /**
     * Get shipment ids
     *
     * @return array
     */
    public function getIds()
    {
        $result = [];
        $this->waitForElementNotVisible($this->loader, Locator::SELECTOR_XPATH);
        $shipmentIds = $this->_rootElement->getElements($this->shipmentId);
        foreach ($shipmentIds as $shipmentId) {
            $result[] = trim($shipmentId->getText());
        }

        return $result;
    }
}
