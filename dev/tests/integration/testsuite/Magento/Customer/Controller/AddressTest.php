<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller;

use Magento\TestFramework\Helper\Bootstrap;

class AddressTest extends \Magento\TestFramework\TestCase\AbstractController
{
    /** @var \Magento\Customer\Api\AccountManagementInterface */
    private $accountManagement;

    protected function setUp()
    {
        parent::setUp();
        $logger = $this->getMock('Psr\Log\LoggerInterface', [], [], '', false);
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Session',
            [$logger]
        );
        $this->accountManagement = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\AccountManagementInterface'
        );
        $customer = $this->accountManagement->authenticate('customer@example.com', 'password');
        $session->setCustomerDataAsLoggedIn($customer);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testIndexAction()
    {
        $this->dispatch('customer/address/index');

        $body = $this->getResponse()->getBody();
        $this->assertContains('Default Billing Address', $body);
        $this->assertContains('Default Shipping Address', $body);
        $this->assertContains('Green str, 67', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testFormAction()
    {
        $this->dispatch('customer/address/edit');

        $body = $this->getResponse()->getBody();
        $this->assertContains('value="John"', $body);
        $this->assertContains('value="Smith"', $body);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_two_addresses.php
     */
    public function testFormPostAction()
    {
        $this->getRequest()->setParam(
            'id',
            2
        )->setMethod(
            'POST'
        )->setPostValue(
            [
                'form_key' => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'James',
                'lastname' => 'Bond',
                'company' => 'Magento Commerce Inc.',
                'telephone' => '1112223333',
                'fax' => '2223334444',
                'street' => ['1234 Monterey Rd', 'Apt 13'],
                'city' => 'Kyiv',
                'region' => 'Kiev',
                'postcode' => '55555',
                'country_id' => 'UA',
                'success_url' => '',
                'error_url' => '',
                'default_billing' => true,
                'default_shipping' => true,
            ]
        );
        // we are overwriting the address coming from the fixture
        $this->dispatch('customer/address/formPost');

        $this->assertRedirect($this->stringContains('customer/address/index'));
        $this->assertSessionMessages(
            $this->equalTo(['You saved the address.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        $address = $this->accountManagement->getDefaultBillingAddress(1);

        $this->assertEquals('UA', $address->getCountryId());
        $this->assertEquals('Kyiv', $address->getCity());
        $this->assertEquals('Kiev', $address->getRegion()->getRegion());
        $this->assertTrue($address->isDefaultBilling());
        $this->assertTrue($address->isDefaultShipping());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testFailedFormPostAction()
    {
        $this->getRequest()->setParam(
            'id',
            1
        )->setMethod(
            'POST'
        )->setPostValue(
            [
                'form_key' => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'James',
                'lastname' => 'Bond',
                'company' => 'Magento Commerce Inc.',
                'telephone' => '1112223333',
                'fax' => '2223334444',
                // omit street and city to fail validation
                'region_id' => '12',
                'region' => 'California',
                'postcode' => '55555',
                'country_id' => 'US',
                'success_url' => '',
                'error_url' => '',
            ]
        );
        // we are overwriting the address coming from the fixture
        $this->dispatch('customer/address/formPost');

        $this->assertRedirect($this->stringContains('customer/address/edit'));
        $this->assertSessionMessages(
            $this->equalTo(
                [
                    'One or more input exceptions have occurred.',
                    'street is a required field.',
                    'city is a required field.',
                ]
            ),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testDeleteAction()
    {
        $this->getRequest()->setParam('id', 1);
        // we are overwriting the address coming from the fixture
        $this->dispatch('customer/address/delete');

        $this->assertRedirect($this->stringContains('customer/address/index'));
        $this->assertSessionMessages(
            $this->equalTo(['You deleted the address.']),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     */
    public function testWrongAddressDeleteAction()
    {
        $this->getRequest()->setParam('id', 555);
        // we are overwriting the address coming from the fixture
        $this->dispatch('customer/address/delete');

        $this->assertRedirect($this->stringContains('customer/address/index'));
        $this->assertSessionMessages(
            $this->equalTo(['We can\'t delete the address right now.']),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
