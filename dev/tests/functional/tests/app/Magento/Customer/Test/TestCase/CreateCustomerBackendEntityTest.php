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
use Magento\Customer\Test\Page\Adminhtml\CustomerIndex;
use Magento\Customer\Test\Page\Adminhtml\CustomerIndexNew;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Fixture\AddressInjectable;

/**
 * Test Coverage for CreateCustomerBackendEntityTest
 *
 * General Flow:
 * 1. Log in as default admin user.
 * 2. Go to Customers > All Customers
 * 3. Press "Add New Customer" button
 * 4. Fill form
 * 5. Click "Save Customer" button
 * 6. Perform all assertions
 *
 * @ticketId MAGETWO-23424
 */
class CreateCustomerBackendEntityTest extends Injectable
{
    /**
     * @var CustomerInjectable
     */
    protected $customer;

    /**
     * @var CustomerIndex
     */
    protected $pageCustomerIndex;

    /**
     * @var CustomerIndexNew
     */
    protected $pageCustomerIndexNew;

    /**
     * @param CustomerIndex $pageCustomerIndex
     * @param CustomerIndexNew $pageCustomerIndexNew
     */
    public function __inject(
        CustomerIndex $pageCustomerIndex,
        CustomerIndexNew $pageCustomerIndexNew
    ) {
        $this->pageCustomerIndex = $pageCustomerIndex;
        $this->pageCustomerIndexNew = $pageCustomerIndexNew;
    }

    /**
     * @param CustomerInjectable $customer
     * @param AddressInjectable $address
     */
    public function testCreateCustomerBackendEntity(CustomerInjectable $customer, AddressInjectable $address)
    {
        // Prepare data
        $address = $address->hasData() ? $address : null;

        // Steps
        $this->pageCustomerIndex->open();
        $this->pageCustomerIndex->getPageActionsBlock()->addNew();
        $this->pageCustomerIndexNew->getCustomerForm()->fillCustomer($customer, $address);
        $this->pageCustomerIndexNew->getPageActionsBlock()->save();
    }
}
