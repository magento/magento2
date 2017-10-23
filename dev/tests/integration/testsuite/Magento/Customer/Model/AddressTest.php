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

        $this->assertEquals(1, $addressData->getId());
        $this->assertEquals('CityX', $addressData->getCity());
        $this->assertEquals('CompanyX', $addressData->getCompany());
        $this->assertEquals('77777', $addressData->getPostcode());
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

        $this->assertEquals(3, $updatedAddressData->getId());
        $this->assertEquals('CityZ', $updatedAddressData->getCity());
        $this->assertEquals('CompanyZ', $updatedAddressData->getCompany());
        $this->assertEquals('99999', $updatedAddressData->getPostcode());
    }
}
