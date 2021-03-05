<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\AccountManagement;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for creation customer with address via customer account management service.
 *
 * @magentoDbIsolation enabled
 */
class CreateAccountWithAddressTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var AccountManagementInterface */
    private $accountManagement;

    /** @var CustomerInterfaceFactory */
    private $customerFactory;

    /** @var CustomerRepositoryInterface */
    private $customerRepository;

    /** @var AddressInterfaceFactory */
    private $addressFactory;

    /** @var CustomerInterface */
    private $customer;

    /** @var Registry */
    private $registry;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->accountManagement = $this->objectManager->get(AccountManagementInterface::class);
        $this->customerFactory = $this->objectManager->get(CustomerInterfaceFactory::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->addressFactory = $this->objectManager->get(AddressInterfaceFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        if ($this->customer instanceof CustomerInterface) {
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', true);
            $this->customerRepository->delete($this->customer);
            $this->registry->unregister('isSecureArea');
            $this->registry->register('isSecureArea', false);
        }

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Store/_files/second_website_with_store_group_and_store.php
     * @magentoConfigFixture default_store general/country/allow BD,BB,AF
     * @magentoConfigFixture fixture_second_store_store general/country/allow AS,BM
     * @return void
     */
    public function testCreateNewCustomerWithAddress(): void
    {
        $availableCountry = 'BD';
        $address = $this->addressFactory->create();
        $address->setCountryId($availableCountry)
            ->setPostcode('75477')
            ->setRegionId(1)
            ->setStreet(['Green str, 67'])
            ->setTelephone('3468676')
            ->setCity('CityM')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setIsDefaultShipping(true)
            ->setIsDefaultBilling(true);
        $customerEntity = $this->customerFactory->create();
        $customerEntity->setEmail('test@example.com')
            ->setFirstname('John')
            ->setLastname('Smith')
            ->setStoreId(1);
        $customerEntity->setAddresses([$address]);
        $this->customer = $this->accountManagement->createAccount($customerEntity);
        $this->assertCount(1, $this->customer->getAddresses(), 'The available address wasn\'t saved.');
        $this->assertSame(
            $availableCountry,
            $this->customer->getAddresses()[0]->getCountryId(),
            'The address was saved with disallowed country.'
        );
    }
}
