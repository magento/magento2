<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model;

class AddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $addressModel;

    /**
     * @var \Magento\Customer\Api\Data\AddressInterfaceFactory
     */
    protected $addressFactory;

    protected function setUp()
    {
        $this->addressModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Model\Address::class
        );
        $this->addressFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Customer\Api\Data\AddressInterfaceFactory::class
        );
    }

    public function testUpdateDataSetDataOnEmptyModel()
    {
        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $addressData = $this->addressFactory->create()
            ->setId(1)
            ->setCity('CityX')
            ->setCompany('CompanyX')
            ->setPostcode('77777');
        $addressData = $this->addressModel->updateData($addressData)->getDataModel();

        $this->assertSame(1, $addressData->getId());
        $this->assertSame('CityX', $addressData->getCity());
        $this->assertSame('CompanyX', $addressData->getCompany());
        $this->assertSame('77777', $addressData->getPostcode());
    }

    public function testUpdateDataOverrideExistingData()
    {
        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $addressData = $this->addressFactory->create()
            ->setId(2)
            ->setCity('CityY')
            ->setCompany('CompanyY')
            ->setPostcode('88888');
        $this->addressModel->updateData($addressData);

        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $updatedAddressData = $this->addressFactory->create()
            ->setId(3)
            ->setCity('CityZ')
            ->setCompany('CompanyZ')
            ->setPostcode('99999');
        $updatedAddressData = $this->addressModel->updateData($updatedAddressData)->getDataModel();

        $this->assertSame(3, $updatedAddressData->getId());
        $this->assertSame('CityZ', $updatedAddressData->getCity());
        $this->assertSame('CompanyZ', $updatedAddressData->getCompany());
        $this->assertSame('99999', $updatedAddressData->getPostcode());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_sample.php
     */
    public function testUpdateDataForExistingCustomer()
    {
        /** @var \Magento\Customer\Model\CustomerRegistry $customerRegistry */
        $customerRegistry = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(CustomerRegistry::class);
        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $updatedAddressData = $this->addressFactory->create()
            ->setId(1)
            ->setCustomerId($customerRegistry->retrieveByEmail('customer@example.com')->getId())
            ->setCity('CityZ')
            ->setCompany('CompanyZ')
            ->setPostcode('99999');
        $updatedAddressData = $this->addressModel->updateData($updatedAddressData)->getDataModel();

        $this->assertSame(1, $updatedAddressData->getId());
        $this->assertSame('CityZ', $updatedAddressData->getCity());
        $this->assertSame('CompanyZ', $updatedAddressData->getCompany());
        $this->assertSame('99999', $updatedAddressData->getPostcode());
        $this->assertSame(true, $updatedAddressData->isDefaultBilling());
        $this->assertSame(true, $updatedAddressData->isDefaultShipping());
    }
}
