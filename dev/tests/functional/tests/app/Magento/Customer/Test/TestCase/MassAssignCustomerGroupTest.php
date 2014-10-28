<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Customer\Test\TestCase;

use Mtf\TestCase\Injectable;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Fixture\CustomerGroupInjectable;

/**
 * Test creation for MassAssignCustomerGroup
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Create customer group
 *
 * Steps:
 * 1. Open Backend
 * 2. Go to Customers> All Customers
 * 3. Find and select(using checkbox) created customer
 * 4. Select "Assign a Customer Group" from action drop-down
 * 5. Select created customer group
 * 6. Click "Submit" button
 * 7. Perform all assertions
 *
 * @group Customer_Groups_(CS), Customers_(CS)
 * @ZephyrId MAGETWO-27892
 */
class MassAssignCustomerGroupTest extends Injectable
{
    /**
     * Customer index page
     *
     * @var CustomerIndex
     */
    protected $customerIndex;

    /**
     * Customers grid actions
     *
     * @var string
     */
    protected $customersGridActions = 'Assign a Customer Group';

    /**
     * Prepare data
     *
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __prepare(CustomerInjectable $customer)
    {
        $customer->persist();

        return ['customer' => $customer];
    }

    /**
     * Injection data
     *
     * @param CustomerIndex $customerIndex
     * @return void
     */
    public function __inject(CustomerIndex $customerIndex)
    {
        $this->customerIndex = $customerIndex;
    }

    /**
     * Mass assign customer group
     *
     * @param CustomerInjectable $customer
     * @param CustomerGroupInjectable $customerGroup
     * @return void
     */
    public function test(CustomerInjectable $customer, CustomerGroupInjectable $customerGroup)
    {
        // Steps
        $customerGroup->persist();
        $this->customerIndex->open();
        $this->customerIndex->getCustomerGridBlock()->massaction(
            [['email' => $customer->getEmail()]],
            [$this->customersGridActions => $customerGroup->getCustomerGroupCode()]
        );
    }
}
