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
namespace Magento\Customer\Service\V1\Data;

use Magento\Customer\Service\V1\AddressMetadataService;
use Magento\Framework\Service\Data\AttributeValue;

class AddressConverterTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    /** @var AddressMetadataService */
    protected $addressMetadataService;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->addressMetadataService = $this->getMockBuilder('Magento\Customer\Service\V1\AddressMetadataService')
            ->setMethods(array('getAttributeMetadata', 'getCustomAttributesMetadata'))
            ->disableOriginalConstructor()
            ->getMock();

        $customAttributeMetadata1 = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $customAttributeMetadata1
            ->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('warehouse_zip'));

        $customAttributeMetadata2 = $this->getMockBuilder('Magento\Customer\Service\V1\Data\Eav\AttributeMetadata')
            ->disableOriginalConstructor()
            ->getMock();
        $customAttributeMetadata2
            ->expects($this->any())
            ->method('getAttributeCode')
            ->will($this->returnValue('warehouse_alternate'));

        $attributesMetadata = array($customAttributeMetadata1, $customAttributeMetadata2);
        $this->addressMetadataService
            ->expects($this->any())
            ->method('getAttributeMetadata')
            ->will($this->returnValue($attributesMetadata));
        $this->addressMetadataService
            ->expects($this->any())
            ->method('getCustomAttributesMetadata')
            ->will($this->returnValue($attributesMetadata));
    }

    public function testToFlatArray()
    {
        $expected = array(
            'id' => 1,
            'default_shipping' => false,
            'default_billing' => true,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => array('7700 W Parmer Ln'),
            'city' => 'Austin',
            'country_id' => 'US',
            'region_id' => 1,
            'region' => 'Texas',
            'region_code' => 'TX'
        );

        $addressData = $this->_sampleAddressDataObject();
        $result = AddressConverter::toFlatArray($addressData);

        $this->assertEquals($expected, $result);
    }

    public function testToFlatArrayCustomAttributes()
    {
        $updatedAddressData = array(
            'email' => 'test@example.com',
            'firstname' => 'John',
            'lastname' => 'Doe',
            'unknown_key' => 'Golden Necklace',
            'custom_attributes' => [
                'warehouse_zip' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_zip',
                    AttributeValue::VALUE => '78777'
                ],
                'warehouse_alternate' => [
                    AttributeValue::ATTRIBUTE_CODE => 'warehouse_alternate',
                    AttributeValue::VALUE => '90051'
                ]
            ]
        );

        $expected = array(
            'id' => 1,
            'default_shipping' => false,
            'default_billing' => true,
            'firstname' => 'John',
            'lastname' => 'Doe',
            'street' => array('7700 W Parmer Ln'),
            'city' => 'Austin',
            'country_id' => 'US',
            'region_id' => 1,
            'region' => 'Texas',
            'region_code' => 'TX',
            'warehouse_zip' => '78777',
            'warehouse_alternate' => '90051'
        );

        $addressData = $this->_sampleAddressDataObject();
        $valueBuilder = $this->_objectManager->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
        /** @var \Magento\Customer\Service\V1\Data\AddressBuilder $addressDataBuilder */
        $addressDataBuilder = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            [
                'valueBuilder' => $valueBuilder,
                'regionBuilder' => $this->_objectManager->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder'),
                'metadataService' => $this->addressMetadataService
            ]
        );
        $addressData = $addressDataBuilder->mergeDataObjectWithArray($addressData, $updatedAddressData);

        $result = AddressConverter::toFlatArray($addressData);
        $this->assertEquals($expected, $result);
    }

    /**
     * @return Address
     */
    protected function _sampleAddressDataObject()
    {
        $regionBuilder = $this->_objectManager->getObject('\Magento\Customer\Service\V1\Data\RegionBuilder')
            ->setRegion('Texas')->setRegionId(1)->setRegionCode('TX');
        $valueBuilder = $this->_objectManager->getObject('Magento\Framework\Service\Data\AttributeValueBuilder');
        /** @var \Magento\Customer\Service\V1\Data\AddressBuilder $addressData */
        $addressData = $this->_objectManager->getObject(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            [
                'valueBuilder' => $valueBuilder,
                'regionBuilder' => $regionBuilder,
                'metadataService' => $this->addressMetadataService
            ]
        )->setId(
                '1'
            )->setDefaultBilling(
                true
            )->setDefaultShipping(
                false
            )->setCity(
                'Austin'
            )->setFirstname(
                'John'
            )->setLastname(
                'Doe'
            )->setCountryId(
                'US'
            )->setStreet(
                array('7700 W Parmer Ln')
            );

        return $addressData->create();
    }
}
