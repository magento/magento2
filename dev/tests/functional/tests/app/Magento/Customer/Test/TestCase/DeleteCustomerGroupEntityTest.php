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

use Magento\Customer\Test\Fixture\CustomerGroupInjectable;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerGroupNew;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteCustomerGroupEntity
 *
 * Test Flow:
 * Preconditions:
 *  1. Customer Group is created
 * Steps:
 *  1. Log in to backend as admin user
 *  2. Navigate to Stores > Other Settings > Customer Groups
 *  3. Click on Customer Group from grid
 *  4. Click on Delete "Customer Group"
 *  5. Confirm in pop-up
 *  6. Perform all assertions
 *
 * @group Customer_Groups_(CS)
 * @ZephyrId MAGETWO-25243
 */
class DeleteCustomerGroupEntityTest extends Injectable
{
    /**
     * Page CustomerGroupIndex
     *
     * @var CustomerGroupIndex
     */
    protected $customerGroupIndex;

    /**
     * Page CustomerGroupNew
     *
     * @var CustomerGroupNew
     */
    protected $customerGroupNew;

    /**
     * Injection data
     *
     * @param CustomerGroupIndex $customerGroupIndex
     * @param CustomerGroupNew $customerGroupNew
     * @return void
     */
    public function __inject(
        CustomerGroupIndex $customerGroupIndex,
        CustomerGroupNew $customerGroupNew
    ) {
        $this->customerGroupIndex = $customerGroupIndex;
        $this->customerGroupNew = $customerGroupNew;
    }

    /**
     * Delete Customer Group
     *
     * @param CustomerGroupInjectable $customerGroup
     * @return void
     */
    public function test(CustomerGroupInjectable $customerGroup)
    {
        // Precondition
        $customerGroup->persist();

        // Steps
        $filter = ['code' => $customerGroup->getCustomerGroupCode()];
        $this->customerGroupIndex->open();
        $this->customerGroupIndex->getCustomerGroupGrid()->searchAndOpen($filter);
        $this->customerGroupNew->getPageMainActions()->delete();
    }
}
