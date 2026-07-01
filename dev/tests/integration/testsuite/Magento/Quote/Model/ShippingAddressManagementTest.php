<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\Customer\Model\Config\Backend\Show\Customer;
use Magento\Quote\Api\Data\AddressInterfaceFactory;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\TestFramework\Fixture\Config as ConfigFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Test for shipping address management
 *
 * @magentoDbIsolation enabled
 */
class ShippingAddressManagementTest extends TestCase
{
    /**
     * @var DataFixtureStorage
     */
    private DataFixtureStorage $fixtures;

    /**
     * @var ShippingAddressManagementInterface
     */
    private ShippingAddressManagementInterface $shippingAddressManagement;

    /**
     * @var AddressInterfaceFactory
     */
    private AddressInterfaceFactory $addressFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->fixtures = $objectManager->get(DataFixtureStorageManager::class)->getStorage();
        $this->shippingAddressManagement = $objectManager->get(ShippingAddressManagementInterface::class);
        $this->addressFactory = $objectManager->get(AddressInterfaceFactory::class);
    }

    /**
     * @return void
     */
    #[
        ConfigFixture(Customer::XML_PATH_CUSTOMER_ADDRESS_SHOW_COMPANY, '0'),
        ConfigFixture(Customer::XML_PATH_CUSTOMER_ADDRESS_SHOW_COMPANY, 'opt', 'store', 'default'),
        DataFixture(GuestCartFixture::class, as: 'cart'),
    ]
    public function testAssignPreservesCompanyWhenCompanyIsEnabledForQuoteStore(): void
    {
        $cartId = (int)$this->fixtures->get('cart')->getId();
        $company = 'Magento Company';
        $address = $this->addressFactory->create();
        $address->setFirstname('John');
        $address->setLastname('Doe');
        $address->setStreet(['Green str, 67']);
        $address->setCity('Montgomery');
        $address->setCountryId('US');
        $address->setRegionId(1);
        $address->setPostcode('36104');
        $address->setTelephone('3340000000');
        $address->setCompany($company);
        $address->setSaveInAddressBook(1);

        $this->shippingAddressManagement->assign($cartId, $address);

        $this->assertSame(
            $company,
            $this->shippingAddressManagement->get($cartId)->getCompany()
        );
    }
}
