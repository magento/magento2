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

use Magento\Customer\Test\Fixture\CustomerInjectable;
use Magento\Customer\Test\Page\CustomerAccountCreate;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Mtf\TestCase\Injectable;

/**
 * Test Creation for CreateExistingCustomerFrontendEntity
 *
 * Test Flow:
 * Preconditions:
 *  1.Customer is created
 * Steps:
 * 1. Go to frontend.
 * 2. Click Register link.
 * 3. Fill registry form.
 * 4. Click 'Create account' button.
 * 5. Perform assertions.
 *
 * @group Customer_Account_(CS)
 * @ZephyrId MAGETWO-23545
 */
class CreateExistingCustomerFrontendEntity extends Injectable
{
    /**
     * Page CustomerAccountCreate
     *
     * @var CustomerAccountCreate
     */
    protected $customerAccountCreate;

    /**
     * Page CustomerAccountLogout
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Page CmsIndex
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Injection data
     *
     * @param CustomerAccountCreate $customerAccountCreate
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CmsIndex $cmsIndex
     * @param CustomerInjectable $customer
     * @return array
     */
    public function __inject(
        CustomerAccountCreate $customerAccountCreate,
        CustomerAccountLogout $customerAccountLogout,
        CmsIndex $cmsIndex,
        CustomerInjectable $customer
    ) {
        $this->customerAccountLogout = $customerAccountLogout;
        $this->customerAccountCreate = $customerAccountCreate;
        $this->cmsIndex = $cmsIndex;
        //Precondition
        $customer->persist();
        return [
            'customer' => $customer,
        ];
    }

    /**
     * Create Existing Customer account on frontend
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    public function testCreateExistingCustomer(CustomerInjectable $customer)
    {
        //Steps
        $this->cmsIndex->open();
        $this->cmsIndex->getLinksBlock()->openLink('Register');
        $this->customerAccountCreate->getRegisterForm()->registerCustomer($customer);
    }

    /**
     * Logout customer from frontend account
     *
     * @return void
     */
    public function tearDown()
    {
        $this->customerAccountLogout->open();
    }
}
