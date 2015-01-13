<?php
/**
 * @spi
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Backend\Test\Block\Widget\Grid;
use Mtf\Fixture\FixtureInterface;

/**
 * Class Customer
 * Customer selection grid
 */
class Customer extends Grid
{
    /**
     * Selector for 'Create New Customer' button
     *
     * @var string
     */
    protected $createNewCustomer = '.actions button';

    /**
     * Locator value for link in action column
     *
     * @var string
     */
    protected $editLink = 'td[data-column=email]';

    /**
     * Filters array mapping
     *
     * @var array
     */
    protected $filters = [
        'email' => [
            'selector' => '#sales_order_create_customer_grid_filter_email',
        ],
    ];

    /**
     * Select customer if it is present in fixture or click create new customer button
     *
     * @param FixtureInterface|null $fixture
     * @return void
     */
    public function selectCustomer($fixture)
    {
        if ($fixture === null) {
            $this->_rootElement->find($this->createNewCustomer)->click();
        } else {
            $this->searchAndOpen(['email' => $fixture->getEmail()]);
        }
        $this->getTemplateBlock()->waitLoader();
    }

    /**
     * Search item and open it.
     *
     * @param array $filter
     * @return void
     */
    public function searchAndOpen(array $filter)
    {
        parent::searchAndOpen($filter);
        $this->getTemplateBlock()->waitLoader();
    }
}
