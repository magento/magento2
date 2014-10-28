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

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Customer\Test\Page\CustomerAccountLogin;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for DeleteCustomerAddress
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Create customer
 * 2. Add default address (NY)
 * 3. Add one more address (CA)
 *
 * Steps:
 * 1. Open frontend
 * 2. Login as customer
 * 3. Go to 'Address Book' tab > Additional Address Entries
 * 4. Delete second address - click 'Delete Address' button
 * 5. Perform all assertions
 *
 * @group Customers_(CS)
 * @ZephyrId MAGETWO-28066
 */
class DeleteCustomerAddressTest extends Injectable
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer login page
     *
     * @var CustomerAccountLogin
     */
    protected $customerAccountLogin;

    /**
     * Customer index page
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * Prepare pages for test
     *
     * @param CustomerAccountLogin $customerAccountLogin
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     * @return void
     */
    public function __inject(
        CustomerAccountLogin $customerAccountLogin,
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex
    ) {
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountLogin = $customerAccountLogin;
        $this->customerAccountIndex = $customerAccountIndex;
    }

    /**
     * Runs Delete Customer Address test
     *
     * @param CustomerInjectable $customer
     * @return array
     */
    public function test(CustomerInjectable $customer)
    {
        // Precondition:
        $customer->persist();
        $addressToDelete = $customer->getDataFieldConfig('address')['source']->getAddresses()[1];

        // Steps:
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink("Log In");
        $this->customerAccountLogin->getLoginBlock()->login($customer);
        $this->customerAccountIndex->getAccountMenuBlock()->openMenuItem('Address Book');
        $this->customerAccountIndex->getAdditionalAddressBlock()->deleteAdditionalAddress($addressToDelete);

        return ['deletedAddress' => $addressToDelete];
    }
}
