<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\TestStep;

use Magento\Customer\Test\Fixture\Customer;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Delete existing customer.
 */
class DeleteCustomerStep implements TestStepInterface
{
    /**
     * @var Customer
     */
    private $customer;

    /**
     * @var CustomerIndex
     */
    private $customerIndexPage;

    /**
     * @var CustomerIndexEdit
     */
    private $customerIndexEditPage;

    /**
     * @param Customer $customer
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     */
    public function __construct(
        Customer $customer,
        CustomerIndex $customerIndexPage,
        CustomerIndexEdit $customerIndexEditPage
    ) {
        $this->customer = $customer;
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $filter = ['email' => $this->customer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getPageActionsBlock()->delete();
        $this->customerIndexEditPage->getModalBlock()->acceptAlert();
    }
}
