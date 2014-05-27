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

use Mtf\Factory\Factory;
use Mtf\TestCase\Functional;
use Magento\Customer\Test\Fixture\Address;
use Magento\Customer\Test\Block\Address\Edit as AddressEditForm;

/**
 * Create Customer on frontend and set default billing address
 */
class CreateOnFrontendTest extends Functional
{
    /**
     * Create Customer account on frontend
     *
     * @ZephyrId MAGETWO-12394
     */
    public function testCreateCustomer()
    {
        //Data
        $customer = Factory::getFixtureFactory()->getMagentoCustomerCustomer();
        $customer->switchData('customer_US_1');
        $customerAddress = $customer->getAddressData();

        //Page
        $homePage = Factory::getPageFactory()->getCmsIndexIndex();
        $createPage = Factory::getPageFactory()->getCustomerAccountCreate();
        $accountIndexPage = Factory::getPageFactory()->getCustomerAccountIndex();
        $addressEditPage = Factory::getPageFactory()->getCustomerAddressEdit();

        //Step 1 Create Account
        $homePage->open();
        $topLinks = $homePage->getLinksBlock();
        $topLinks->openLink('Register');

        $createPage->getRegisterForm()->registerCustomer($customer);

        //Verifying
        $this->assertContains('Thank you for registering', $accountIndexPage->getMessages()->getSuccessMessages());

        //Check that customer redirected to Dashboard after registration
        $this->assertContains('My Dashboard', $accountIndexPage->getTitleBlock()->getTitle());

        //Step 2 Set Billing Address
        $accountIndexPage->getDashboardAddress()->editBillingAddress();
        $addressEditPage->getEditForm()->editCustomerAddress($customerAddress);

        //Verifying
        $accountIndexPage = Factory::getPageFactory()->getCustomerAccountIndex();
        $this->assertContains('The address has been saved', $accountIndexPage->getMessages()->getSuccessMessages());

        //Verify customer address against previously entered data
        $accountIndexPage->open();
        $accountIndexPage->getDashboardAddress()->editBillingAddress();
        $addressEditPage = Factory::getPageFactory()->getCustomerAddressEdit();
        $this->verifyCustomerAddress($customerAddress, $addressEditPage->getEditForm());
    }

    /**
     * Verify that customer address is equals data on form
     *
     * @param Address $address
     * @param AddressEditForm $form
     * @return bool
     */
    protected function verifyCustomerAddress(Address $address, AddressEditForm $form)
    {
        $dataAddress = $address->getData();
        $preparedDataAddress = [];

        foreach ($dataAddress['fields'] as $key => $field) {
            $preparedDataAddress[$key] = $field['value'];
        }

        $dataDiff = array_diff($preparedDataAddress, $form->getData($address));
        $this->assertTrue(
            empty($dataDiff),
            'Customer addresses data on edit page(backend) not equals to passed from fixture.'
        );
    }
}
