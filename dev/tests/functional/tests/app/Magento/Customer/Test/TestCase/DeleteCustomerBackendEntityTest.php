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
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexEdit;

/**
 * Test creation for DeleteCustomerBackendEntity
 *
 * Test Flow:
 * Preconditions:
 * 1. Create customer on the backend
 *
 * Steps:
 * 1. Open backend
 * 2. Go to  Customers - All Customers
 * 3. Search and open created customer according to dataset
 * 4. Fill in data according to dataset
 * 5. Perform all assertions according to dataset
 *
 * @group Customers_(CS)
 * @ZephyrId MAGETWO-24764
 */
class DeleteCustomerBackendEntityTest extends Injectable
{
    /**
     * @var CustomerIndex
     */
    protected $customerIndexPage;

    /**
     * @var CustomerIndexEdit
     */
    protected $customerIndexEditPage;

    /**
     * Preparing pages for test
     *
     * @param CustomerIndex $customerIndexPage
     * @param CustomerIndexEdit $customerIndexEditPage
     * @return void
     */
    public function __inject(CustomerIndex $customerIndexPage, CustomerIndexEdit $customerIndexEditPage)
    {
        $this->customerIndexPage = $customerIndexPage;
        $this->customerIndexEditPage = $customerIndexEditPage;
    }

    /**
     * Runs Delete Customer Backend Entity test
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    public function testDeleteCustomerBackendEntity(CustomerInjectable $customer)
    {
        // Preconditions:
        $customer->persist();

        // Steps:
        $filter = ['email' => $customer->getEmail()];
        $this->customerIndexPage->open();
        $this->customerIndexPage->getCustomerGridBlock()->searchAndOpen($filter);
        $this->customerIndexEditPage->getPageActionsBlock()->delete();
    }
}
