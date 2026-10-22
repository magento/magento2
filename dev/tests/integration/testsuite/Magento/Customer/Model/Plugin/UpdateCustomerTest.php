<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Model\Plugin;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\ResourceModel\AddressRepository;
use Magento\Customer\Test\Fixture\CustomerWithAddresses;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Verify that partial REST updates preserve addresses without synchronizing them.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class UpdateCustomerTest extends TestCase
{
    /**
     * @param bool $changeDefaults
     * @return void
     * @magentoDbIsolation enabled
     */
    #[DataFixture(CustomerWithAddresses::class, as: 'customer')]
    #[DataProvider('defaultAddressDataProvider')]
    public function testAccountUpdateDoesNotSaveAddresses(bool $changeDefaults): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $customerId = (int)DataFixtureStorageManager::getStorage()->get('customer')->getId();
        $addressRepository = $this->createMock(AddressRepository::class);
        $addressRepository->expects($this->never())->method('save');
        $addressRepository->expects($this->never())->method('deleteById');
        $customerRepository = $objectManager->create(
            CustomerRepositoryInterface::class,
            ['addressRepository' => $addressRepository]
        );
        $originalCustomer = $customerRepository->getById($customerId);
        $originalAddresses = array_map(
            static fn (AddressInterface $address) => $address->__toArray(),
            $originalCustomer->getAddresses()
        );
        $this->assertCount(3, $originalAddresses);
        $defaultBilling = $originalCustomer->getDefaultBilling();
        $defaultShipping = $originalCustomer->getDefaultShipping();
        $incomingCustomer = $objectManager->get(CustomerInterfaceFactory::class)->create();
        $incomingCustomer->setId($customerId)->setFirstname('Updated');
        if ($changeDefaults) {
            $defaultBilling = (string)$originalCustomer->getAddresses()[1]->getId();
            $defaultShipping = (string)$originalCustomer->getAddresses()[2]->getId();
            $incomingCustomer->setDefaultBilling($defaultBilling)->setDefaultShipping($defaultShipping);
        }

        $request = $this->createStub(Request::class);
        $request->method('getParam')->willReturn((string)$customerId);
        $request->method('getBodyParams')->willReturn([]);
        $userContext = $this->createStub(UserContextInterface::class);
        $userContext->method('getUserType')->willReturn(UserContextInterface::USER_TYPE_ADMIN);
        $userContext->method('getUserId')->willReturn(0);
        $plugin = new UpdateCustomer($request, $userContext);
        [$customer] = $plugin->beforeSave($customerRepository, $incomingCustomer);
        $savedCustomer = $customerRepository->save($customer);

        $this->assertSame('Updated', $savedCustomer->getFirstname());
        $this->assertEquals($defaultBilling, $savedCustomer->getDefaultBilling());
        $this->assertEquals($defaultShipping, $savedCustomer->getDefaultShipping());
        $savedAddresses = array_map(
            static fn (AddressInterface $address) => $address->__toArray(),
            $savedCustomer->getAddresses()
        );
        if ($changeDefaults) {
            foreach ($savedAddresses as $index => $address) {
                $originalAddresses[$index]['default_billing'] = $address['id'] == $defaultBilling;
                $originalAddresses[$index]['default_shipping'] = $address['id'] == $defaultShipping;
            }
        }
        $this->assertEquals($originalAddresses, $savedAddresses);
    }

    /**
     * @return array
     */
    public static function defaultAddressDataProvider(): array
    {
        return [
            'preserve defaults' => [false],
            'change defaults without an address list' => [true],
        ];
    }
}
