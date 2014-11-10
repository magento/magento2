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

namespace Magento\Customer\Model;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Customer\Model\Address
     */
    protected $addressModel;

    /**
     * @var \Magento\Customer\Api\Data\AddressDataBuilder
     */
    protected $addressBuilder;

    protected function setUp()
    {
        $this->addressModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Model\Address'
        );
        $this->addressBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Customer\Api\Data\AddressDataBuilder'
        );
    }

    public function testUpdateDataSetDataOnEmptyModel()
    {
        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $addressData = $this->addressBuilder
            ->setId(1)
            ->setCity('CityX')
            ->setCompany('CompanyX')
            ->setPostcode('77777')
            ->create();
        $addressData = $this->addressModel->updateData($addressData)->getDataModel();

        $this->assertEquals(1, $addressData->getId());
        $this->assertEquals('CityX', $addressData->getCity());
        $this->assertEquals('CompanyX', $addressData->getCompany());
        $this->assertEquals('77777', $addressData->getPostcode());
    }

    public function testUpdateDataOverrideExistingData()
    {
        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $addressData = $this->addressBuilder
            ->setId(2)
            ->setCity('CityY')
            ->setCompany('CompanyY')
            ->setPostcode('88888')
            ->create();
        $this->addressModel->updateData($addressData);

        /** @var \Magento\Customer\Model\Data\Address $addressData */
        $updatedAddressData = $this->addressBuilder
            ->setId(3)
            ->setCity('CityZ')
            ->setCompany('CompanyZ')
            ->setPostcode('99999')
            ->create();
        $updatedAddressData = $this->addressModel->updateData($updatedAddressData)->getDataModel();

        $this->assertEquals(3, $updatedAddressData->getId());
        $this->assertEquals('CityZ', $updatedAddressData->getCity());
        $this->assertEquals('CompanyZ', $updatedAddressData->getCompany());
        $this->assertEquals('99999', $updatedAddressData->getPostcode());
    }
}
