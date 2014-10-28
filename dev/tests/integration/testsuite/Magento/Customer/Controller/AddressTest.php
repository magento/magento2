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
namespace Magento\Customer\Controller;

use Magento\TestFramework\Helper\Bootstrap;

class AddressTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected function setUp()
    {
        parent::setUp();
        $logger = $this->getMock('Magento\Framework\Logger', array(), array(), '', false);
        $session = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Session',
            array($logger)
        );
        $service = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerAccountService'
        );
        $customer = $service->authenticate('customer@example.com', 'password');
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
        $this->assertContains('value="Firstname"', $body);
        $this->assertContains('value="Lastname"', $body);
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
        )->setServer(
            array('REQUEST_METHOD' => 'POST')
        )->setPost(
            array(
                'form_key' => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'James',
                'lastname' => 'Bond',
                'company' => 'Ebay',
                'telephone' => '1112223333',
                'fax' => '2223334444',
                'street' => array('1234 Monterey Rd', 'Apt 13'),
                'city' => 'Kyiv',
                'region' => 'Kiev',
                'postcode' => '55555',
                'country_id' => 'UA',
                'success_url' => '',
                'error_url' => ''
            )
        );
        // we are overwriting the address coming from the fixture
        $this->dispatch('customer/address/formPost');

        $this->assertRedirect($this->stringContains('customer/address/index'));
        $this->assertSessionMessages(
            $this->equalTo(array('The address has been saved.')),
            \Magento\Framework\Message\MessageInterface::TYPE_SUCCESS
        );
        /** @var \Magento\Customer\Service\V1\CustomerAddressService $addressService */
        $addressService = Bootstrap::getObjectManager()->create('Magento\Customer\Service\V1\CustomerAddressService');
        $address = $addressService->getAddress(2);

        $this->assertEquals('UA', $address->getCountryId());
        $this->assertEquals('Kyiv', $address->getCity());
        $this->assertEquals('Kiev', $address->getRegion()->getRegion());
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
        )->setServer(
            array('REQUEST_METHOD' => 'POST')
        )->setPost(
            array(
                'form_key' => $this->_objectManager->get('Magento\Framework\Data\Form\FormKey')->getFormKey(),
                'firstname' => 'James',
                'lastname' => 'Bond',
                'company' => 'Ebay',
                'telephone' => '1112223333',
                'fax' => '2223334444',
                // omit street and city to fail validation
                'region_id' => '12',
                'region' => 'California',
                'postcode' => '55555',
                'country_id' => 'US',
                'success_url' => '',
                'error_url' => ''
            )
        );
        // we are overwriting the address coming from the fixture
        $this->dispatch('customer/address/formPost');

        $this->assertRedirect($this->stringContains('customer/address/edit'));
        $this->assertSessionMessages(
            $this->equalTo(
                array(
                    'One or more input exceptions have occurred.',
                    'street is a required field.',
                    'city is a required field.'
                )
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
            $this->equalTo(array('The address has been deleted.')),
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
            $this->equalTo(array('An error occurred while deleting the address.')),
            \Magento\Framework\Message\MessageInterface::TYPE_ERROR
        );
    }
}
