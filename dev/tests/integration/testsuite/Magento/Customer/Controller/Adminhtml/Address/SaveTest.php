<?php
declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * @magentoAppArea adminhtml
 */
class SaveTest extends \Magento\TestFramework\TestCase\AbstractBackendController
{
    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var \Magento\TestFramework\ObjectManager */
    private $objectManager;

    /** @var \Magento\Customer\Controller\Adminhtml\Address\Save */
    private $customerAddress;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->customerRepository = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\CustomerRepositoryInterface::class
        );
        $this->accountManagement = Bootstrap::getObjectManager()->get(
            \Magento\Customer\Api\AccountManagementInterface::class
        );
        $this->objectManager = Bootstrap::getObjectManager();
        $this->customerAddress = $this->objectManager->get(\Magento\Customer\Controller\Adminhtml\Address\Save::class);
    }

    /**
     * @inheritDoc
     */
    protected function tearDown()
    {
        /**
         * Unset customer data
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->setCustomerData(null);

        /**
         * Unset messages
         */
        Bootstrap::getObjectManager()->get(\Magento\Backend\Model\Session::class)->getMessages(true);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     *
     * Check that customer id set and addresses saved
     */
    public function testSaveActionWithValidAddressData()
    {
        $customer = $this->customerRepository->get('customer5@example.com');
        $customerId = $customer->getId();
        $post = [
            'parent_id' => $customerId,
            'firstname' => 'test firstname',
            'lastname' => 'test lastname',
            'street' => ['test street'],
            'city' => 'test city',
            'region_id' => 10,
            'country_id' => 'US',
            'postcode' => '01001',
            'telephone' => '+7000000001',
        ];
        $this->getRequest()->setPostValue($post)->setMethod(HttpRequest::METHOD_POST);

        $this->objectManager->get(\Magento\Backend\Model\Session::class)->setCustomerFormData($post);

        $this->customerAddress->execute();

        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /** Check that customer data were cleaned after it was saved successfully*/
        $this->assertEmpty($this->objectManager->get(\Magento\Backend\Model\Session::class)->getCustomerData());

        $customer = $this->customerRepository->getById($customerId);

        $this->assertEquals('Firstname', $customer->getFirstname());
        $addresses = $customer->getAddresses();
        $this->assertCount(1, $addresses);
        $this->assertNull($this->accountManagement->getDefaultBillingAddress($customerId));
        $this->assertNull($this->accountManagement->getDefaultShippingAddress($customerId));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_no_address.php
     *
     * Check that customer id set and addresses saved
     */
    public function testSaveActionWithDefaultShippingAndBilling()
    {
        $customer = $this->customerRepository->get('customer5@example.com');
        $customerId = $customer->getId();
        $post = [
            'parent_id' => $customerId,
            'firstname' => 'test firstname',
            'lastname' => 'test lastname',
            'street' => ['test street'],
            'city' => 'test city',
            'region_id' => 10,
            'country_id' => 'US',
            'postcode' => '01001',
            'telephone' => '+7000000001',
            'default_billing' => true,
            'default_shipping' => true
        ];
        $this->getRequest()->setPostValue($post)->setMethod(HttpRequest::METHOD_POST);

        $this->objectManager->get(\Magento\Backend\Model\Session::class)->setCustomerFormData($post);

        $this->customerAddress->execute();
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that customer data were cleaned after it was saved successfully
         */
        $this->assertEmpty($this->objectManager->get(\Magento\Backend\Model\Session::class)->getCustomerData());

        /**
         * Remove stored customer from registry
         */
        $this->objectManager->get(\Magento\Customer\Model\CustomerRegistry::class)->remove($customerId);
        $customer = $this->customerRepository->get('customer5@example.com');
        $this->assertEquals('Firstname', $customer->getFirstname());
        $addresses = $customer->getAddresses();
        $this->assertCount(1, $addresses);

        $this->assertNotNull($this->accountManagement->getDefaultBillingAddress($customerId));
        $this->assertNotNull($this->accountManagement->getDefaultShippingAddress($customerId));
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     *
     * Check that customer id set and addresses saved
     */
    public function testSaveActionWithExistingAdresses()
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $customerId = $customer->getId();
        $post = [
            'parent_id' => $customerId,
            'firstname' => 'test firstname',
            'lastname' => 'test lastname',
            'street' => ['test street'],
            'city' => 'test city',
            'region_id' => 10,
            'country_id' => 'US',
            'postcode' => '01001',
            'telephone' => '+7000000001',
        ];
        $this->getRequest()->setPostValue($post)->setMethod(HttpRequest::METHOD_POST);

        $this->objectManager->get(\Magento\Backend\Model\Session::class)->setCustomerFormData($post);

        $this->customerAddress->execute();
        /**
         * Check that errors was generated and set to session
         */
        $this->assertSessionMessages($this->isEmpty(), \Magento\Framework\Message\MessageInterface::TYPE_ERROR);

        /**
         * Check that customer data were cleaned after it was saved successfully
         */
        $this->assertEmpty($this->objectManager->get(\Magento\Backend\Model\Session::class)->getCustomerData());

        $customer = $this->customerRepository->getById($customerId);

        $this->assertEquals('test firstname', $customer->getFirstname());
        $addresses = $customer->getAddresses();
        $this->assertCount(4, $addresses);
    }
}
