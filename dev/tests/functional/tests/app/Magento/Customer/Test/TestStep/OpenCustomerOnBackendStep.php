<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Customer\Test\TestStep;

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Mtf\TestStep\TestStepInterface;

/**
 * Class OpenCustomerOnBackendStep
 * Open customer account
 */
class OpenCustomerOnBackendStep implements TestStepInterface
{
    /**
     * Customer fixture
     *
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * Customer index page
     *
     * @var CustomerInjectable
     */
    protected $customerIndex;

    /**
     * @constructor
     * @param CustomerInjectable $customer
     * @param CustomerIndex $customerIndex
     */
    public function __construct(CustomerInjectable $customer, CustomerIndex $customerIndex)
    {
        $this->customer = $customer;
        $this->customerIndex = $customerIndex;
    }

    /**
     * Open customer account
     *
     * @return void
     */
    public function run()
    {
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->searchAndOpen(['email' => $this->customer->getEmail()]);
    }
}
