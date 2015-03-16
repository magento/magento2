<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order;

use Magento\Backend\Test\Block\Widget\Grid as GridInterface;
use Magento\Mtf\Client\Locator;

/**
 * Sales order grid.
 */
class Grid extends GridInterface
{
    /**
     * 'Add New' order button.
     *
     * @var string
     */
    protected $addNewOrder = "../*[@class='page-actions']//*[@id='add']";

    /**
     * Purchase Point Filter selector.
     *
     * @var string
     */
    protected $purchasePointFilter = '//*[@data-ui-id="widget-grid-column-filter-store-0-filter-store-id"]';

    /**
     * Purchase Point Filter option group elements selector.
     *
     * @var string
     */
    protected $purchasePointOptGroup = '//*[@data-ui-id="widget-grid-column-filter-store-0-filter-store-id"]/optgroup';

    /**
     * Order Id td selector.
     *
     * @var string
     */
    protected $editLink = 'td[class*=col-action] a';

    /**
     * First row selector.
     *
     * @var string
     */
    protected $firstRowSelector = '//tbody/tr[1]//a';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'id' => [
            'selector' => 'input[name="real_order_id"]',
        ],
        'status' => [
            'selector' => 'select[name="status"]',
            'input' => 'select',
        ],
    ];

    /**
     * Start to create new order.
     */
    public function addNewOrder()
    {
        $this->_rootElement->find($this->addNewOrder, Locator::SELECTOR_XPATH)->click();
    }

    /**
     * Get StoreGroup list of Purchase Point on filter.
     *
     * @return array
     */
    public function getPurchasePointStoreGroups()
    {
        $storeGroupElements = $this->_rootElement->find($this->purchasePointFilter, Locator::SELECTOR_XPATH)
            ->getElements('.//optgroup[./option]', Locator::SELECTOR_XPATH);
        $result = [];

        foreach ($storeGroupElements as $storeGroupElement) {
            $result[] = trim($storeGroupElement->getAttribute('label'), ' ');
        }

        return $result;
    }
}
