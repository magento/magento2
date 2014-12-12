<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Mtf\TestStep\TestStepInterface;

/**
 * Class CreateOrderFromCustomerAccountStep
 * Create order from customer page on Backend
 */
class CreateOrderFromCustomerAccountStep implements TestStepInterface
{
    /**
     * Customer edit page
     *
     * @var CustomerIndexEdit
     */
    protected $customerIndexEdit;

    /**
     * @constructor
     * @param CustomerIndexEdit $customerIndexEdit
     */
    public function __construct(CustomerIndexEdit $customerIndexEdit)
    {
        $this->customerIndexEdit = $customerIndexEdit;
    }

    /**
     * Create new order from customer step
     *
     * @return void
     */
    public function run()
    {
        $this->customerIndexEdit->getPageActionsBlock()->createOrder();
    }
}
