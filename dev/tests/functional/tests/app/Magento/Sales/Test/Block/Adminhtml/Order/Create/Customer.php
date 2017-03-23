<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Block\Adminhtml\Order\Create;

use Magento\Backend\Test\Block\Widget\Grid;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;

/**
 * Customer selection grid.
 */
class Customer extends Grid
{
    /**
     * Selector for 'Create New Customer' button.
     *
     * @var string
     */
    protected $createNewCustomer = '.actions button';

    /**
     * Locator value for link in action column.
     *
     * @var string
     */
    protected $editLink = 'td[data-column=email]';

    /**
     * Filters array mapping.
     *
     * @var array
     */
    protected $filters = [
        'email' => [
            'selector' => '#sales_order_create_customer_grid_filter_email',
        ],
    ];

    /**
     * Select customer if it is present in fixture or click create new customer button.
     *
     * @param CustomerFixture $customer
     * @return void
     */
    public function selectCustomer(CustomerFixture $customer)
    {
        if ($customer->hasData('id')) {
            $this->searchAndOpen(['email' => $customer->getEmail()]);
        } else {
            $this->_rootElement->find($this->createNewCustomer)->click();
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
