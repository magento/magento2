<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Address\Delete;

use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test cases related to check that customer address correctly deleted on frontend
 * or wasn't deleted and proper error message appears.
 *
 * @magentoAppArea frontend
 * @magentoDbIsolation enabled
 *
 * @see \Magento\Customer\Controller\Address\Delete::execute
 */
class DeleteAddressTest extends AbstractController
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var AddressRepositoryInterface
     */
    private $addressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->escaper = $this->_objectManager->get(Escaper::class);
        $this->customerSession = $this->_objectManager->get(Session::class);
        $this->addressRepository = $this->_objectManager->get(AddressRepositoryInterface::class);
        $this->customerRepository = $this->_objectManager->get(CustomerRepositoryInterface::class);
    }

    /**
     * Assert that customer address deleted successfully.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @return void
     */
    public function testSuccessDeleteExistCustomerAddress(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $customerAddresses = $customer->getAddresses() ?? [];
        $this->assertCount(1, $customerAddresses);
        /** @var AddressInterface $currentCustomerAddress */
        $currentCustomerAddress = reset($customerAddresses);
        $this->customerSession->setCustomerId($customer->getId());
        $this->performAddressDeleteRequest((int)$currentCustomerAddress->getId());
        $this->checkRequestPerformedSuccessfully();
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertCount(0, $customer->getAddresses() ?? []);
        try {
            $this->addressRepository->getById((int)$currentCustomerAddress->getId());
            $this->fail('Customer address is not deleted.');
        } catch (LocalizedException $e) {
            //Do nothing, this block mean that address deleted successfully from DB.
        }
    }

    /**
     * Check that customer address will not be deleted if we don't pass address ID parameter.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     *
     * @return void
     */
    public function testDeleteWithoutParam(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $customerAddresses = $customer->getAddresses() ?? [];
        $this->assertCount(1, $customerAddresses);
        /** @var AddressInterface $currentCustomerAddress */
        $currentCustomerAddress = reset($customerAddresses);
        $this->customerSession->setCustomerId($customer->getId());
        $this->performAddressDeleteRequest();
        $this->assertRedirect($this->stringContains('customer/address/index'));
        $customer = $this->customerRepository->get('customer@example.com');
        $this->assertCount(1, $customer->getAddresses() ?? []);
        $this->checkAddressWasntDeleted((int)$currentCustomerAddress->getId());
    }

    /**
     * Check that customer address will not be deleted if customer id in address and in session are not equals.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @magentoDataFixture Magento/Customer/_files/customer_address.php
     * @magentoDataFixture Magento/Customer/_files/customer_with_uk_address.php
     *
     * @return void
     */
    public function testDeleteDifferentCustomerAddress(): void
    {
        $firstCustomer = $this->customerRepository->get('customer@example.com');
        $customerAddresses = $firstCustomer->getAddresses() ?? [];
        $this->assertCount(1, $customerAddresses);
        /** @var AddressInterface $currentCustomerAddress */
        $currentCustomerAddress = reset($customerAddresses);
        $this->customerSession->setCustomerId('1');
        $secondCustomer = $this->customerRepository->get('customer_uk_address@test.com');
        $secondCustomerAddresses = $secondCustomer->getAddresses() ?? [];
        /** @var AddressInterface $secondCustomerAddress */
        $secondCustomerAddress = reset($secondCustomerAddresses);
        $this->performAddressDeleteRequest((int)$secondCustomerAddress->getId());
        $this->checkRequestPerformedWithError(true);
        $firstCustomer = $this->customerRepository->get('customer@example.com');
        $this->assertCount(1, $firstCustomer->getAddresses() ?? []);
        $this->checkAddressWasntDeleted((int)$currentCustomerAddress->getId());
    }

    /**
     * Check that error message appear if we try to delete non-exits address.
     *
     * @magentoDataFixture Magento/Customer/_files/customer.php
     *
     * @return void
     */
    public function testDeleteNonExistAddress(): void
    {
        $customer = $this->customerRepository->get('customer@example.com');
        $this->customerSession->setCustomerId($customer->getId());
        $this->performAddressDeleteRequest(999);
        $this->checkRequestPerformedWithError();
    }

    /**
     * Perform delete request by provided address id.
     *
     * @param int|null $processAddressId
     * @return void
     */
    private function performAddressDeleteRequest(?int $processAddressId = null): void
    {
        $this->getRequest()->setMethod(Http::METHOD_POST);
        if (null !== $processAddressId) {
            $this->getRequest()->setPostValue(['id' => $processAddressId]);
        }
        $this->dispatch('customer/address/delete');
    }

    /**
     * Check that delete address request performed successfully
     * (proper success message and redirect to customer/address/index are appear).
     *
     * @return void
     */
    private function checkRequestPerformedSuccessfully(): void
    {
        $this->assertRedirect($this->stringContains('customer/address/index'));
        $this->assertSessionMessages(
            $this->equalTo([(string)__('You deleted the address.')]),
            MessageInterface::TYPE_SUCCESS
        );
    }

    /**
     * Check that delete address request performed with error.
     * (proper error messages and redirect to customer/address/edit are appear).
     *
     * @param bool $isNeedEscapeMessage
     * @return void
     */
    private function checkRequestPerformedWithError(bool $isNeedEscapeMessage = false): void
    {
        $message = (string)__("We can't delete the address right now.");
        if ($isNeedEscapeMessage) {
            $message = $this->escaper->escapeHtml($message);
        }
        $this->assertSessionMessages($this->containsEqual($message), MessageInterface::TYPE_ERROR);
    }

    /**
     * Assert that customer address wasn't deleted.
     *
     * @param int $addressId
     * @return void
     */
    private function checkAddressWasntDeleted(int $addressId): void
    {
        try {
            $this->addressRepository->getById($addressId);
        } catch (LocalizedException $e) {
            $this->fail('Expects that customer address will not be deleted.');
        }
    }
}
