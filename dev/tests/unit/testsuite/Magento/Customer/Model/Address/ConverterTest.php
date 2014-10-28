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
namespace Magento\Customer\Model\Address;

use Magento\Framework\Service\Data\AttributeValue;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Converter
     */
    protected $model;

    /**
     * @var \Magento\Customer\Service\V1\Data\AddressBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressBuilderMock;

    /**
     * @var \Magento\Customer\Model\AddressFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressFactoryMock;

    /**
     * @var \Magento\Customer\Service\V1\Data\RegionBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $regionBuilderMock;

    /**
     *
     * @var \Magento\Customer\Service\V1\AddressMetadataServiceInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $addressMetadataServiceMock;

    protected function setUp()
    {
        $this->addressBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\AddressBuilder',
            array('populateWithArray', 'setId', 'setCustomerId', 'create'),
            array(),
            '',
            false
        );

        $this->addressFactoryMock = $this->getMock(
            'Magento\Customer\Model\AddressFactory',
            array('create'),
            array(),
            '',
            false
        );

        $this->regionBuilderMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\RegionBuilder',
            array(),
            array(),
            '',
            false
        );

        $this->addressMetadataServiceMock = $this->getMock(
            'Magento\Customer\Service\V1\AddressMetadataService',
            array('getAllAttributesMetadata'),
            array(),
            '',
            false
        );

        $this->model = new Converter(
            $this->addressBuilderMock,
            $this->addressFactoryMock,
            $this->addressMetadataServiceMock
        );
    }

    public function testUpdateAddressModel()
    {
        $addressModelMock = $this->getAddressModelMock();
        $addressModelMock->expects($this->once())
            ->method('getAttributeSetId')
            ->will($this->returnValue(false));
        $addressModelMock->expects($this->once())
            ->method('setAttributeSetId')
            ->with($this->equalTo(
                \Magento\Customer\Service\V1\AddressMetadataServiceInterface::ATTRIBUTE_SET_ID_ADDRESS
            ));

        $addressMock = $this->getMock('Magento\Customer\Service\V1\Data\Address', array(), array(), '', false);
        $addressMock->expects($this->once())
            ->method('__toArray')
            ->will($this->returnValue(array()));

        $this->model->updateAddressModel($addressModelMock, $addressMock);
    }

    public function testUpdateAddressModelWithAttributes()
    {
        $addressModelMock = $this->getAddressModelMock();
        $addressModelMock->expects($this->once())
            ->method('getAttributeSetId')
            ->will($this->returnValue(true));
        $addressModelMock->expects($this->never())
            ->method('setAttributeSetId');

        $attributes = array(
            'custom_attributes' => array(
                array(AttributeValue::ATTRIBUTE_CODE => 'code_01', AttributeValue::VALUE => 'value_01'),
                array(AttributeValue::ATTRIBUTE_CODE => 'code_02', AttributeValue::VALUE => 'value_02'),
                array(AttributeValue::ATTRIBUTE_CODE => 'code_03', AttributeValue::VALUE => 'value_03'),
            ),
            'attributes_01' => array('some_value_01', 'some_value_02', 'some_value_03'),
            'attributes_02' => 'some_value_04',
            \Magento\Customer\Service\V1\Data\Address::KEY_REGION => 'some_region',
        );
        $regionMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\Region',
            array('getRegion', 'getRegionCode', 'getRegionId'),
            array(),
            '',
            false
        );
        $regionMock->expects($this->once())->method('getRegion');
        $regionMock->expects($this->once())->method('getRegionCode');
        $regionMock->expects($this->once())->method('getRegionId');
        $addressMock = $this->getMock('Magento\Customer\Service\V1\Data\Address', array(), array(), '', false);
        $addressMock->expects($this->once())
            ->method('__toArray')
            ->will($this->returnValue($attributes));
        $addressMock->expects($this->exactly(4))
            ->method('getRegion')
            ->will($this->returnValue($regionMock));

        $this->model->updateAddressModel($addressModelMock, $addressMock);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAddressModelMock()
    {
        $addressModelMock = $this->getMock(
            'Magento\Customer\Model\Address',
            array('setIsDefaultBilling', 'setIsDefaultShipping', 'setAttributeSetId', 'getAttributeSetId', '__wakeup'),
            array(),
            '',
            false
        );
        $addressModelMock->expects($this->once())
            ->method('setIsDefaultBilling');
        $addressModelMock->expects($this->once())
            ->method('setIsDefaultShipping');
        return $addressModelMock;
    }

    public function testCreateAddressFromModel()
    {
        $defaultBillingId = 1;
        $defaultShippingId = 1;
        $addressId = 1;

        $addressModelMock = $this->getAddressMockForCreate();
        $addressModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($addressId));
        $addressModelMock->expects($this->any())
            ->method('getCustomerId');
        $addressModelMock->expects($this->any())
            ->method('getParentId');

        $addressMock = $this->getMock('Magento\Customer\Service\V1\Data\Address', array(), array(), '', false);
        $this->addressMetadataServiceMock->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->will($this->returnValue(array()));
        $this->addressBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($addressMock));
        $this->addressBuilderMock->expects($this->once())
            ->method('setId')
            ->with($this->equalTo($addressId));
        $this->addressBuilderMock->expects($this->never())
            ->method('setCustomerId');
        $this->assertEquals(
            $addressMock,
            $this->model->createAddressFromModel($addressModelMock, $defaultBillingId, $defaultShippingId)
        );
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function testCreateAddressFromModelWithCustomerId()
    {
        $defaultBillingId = 1;
        $defaultShippingId = 1;
        $customerId = 1;
        $attributeCode = 'attribute_code';

        $addressModelMock = $this->getAddressMockForCreate();
        $addressModelMock->expects($this->once())
            ->method('getId')
            ->will($this->returnValue(null));
        $addressModelMock->expects($this->any())
            ->method('getCustomerId')
            ->will($this->returnValue($customerId));
        $addressModelMock->expects($this->any())
            ->method('getParentId');
        $getData = function ($key, $index = null) use ($attributeCode, $customerId) {
            $result = null;
            switch($key) {
                case $attributeCode:
                    $result = 'some_data';
                    break;
                case 'customer_id':
                    $result = $customerId;
                    break;
            }
            return $result;
        };
        $addressModelMock->expects($this->any())
            ->method('getData')
            ->will($this->returnCallback($getData));
        $attributeMock = $this->getMock(
            'Magento\Customer\Service\V1\Data\Eav\AttributeMetadata',
            array('getAttributeCode'),
            array(),
            '',
            false
        );
        $attributeMock->expects($this->once())
            ->method('getAttributeCode')
            ->will($this->returnValue($attributeCode));


        $addressMock = $this->getMock('Magento\Customer\Service\V1\Data\Address', array(), array(), '', false);
        $this->addressMetadataServiceMock->expects($this->once())
            ->method('getAllAttributesMetadata')
            ->will($this->returnValue(array($attributeMock)));
        $this->addressBuilderMock->expects($this->once())
            ->method('create')
            ->will($this->returnValue($addressMock));
        $this->addressBuilderMock->expects($this->once())
            ->method('setCustomerId')
            ->with($this->equalTo($customerId));
        $this->assertEquals(
            $addressMock,
            $this->model->createAddressFromModel($addressModelMock, $defaultBillingId, $defaultShippingId)
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getAddressMockForCreate()
    {
        $addressModelMock = $this->getMockForAbstractClass(
            'Magento\Customer\Model\Address\AbstractAddress',
            array(),
            '',
            false,
            false,
            false,
            array(
                'getId',
                'getStreet',
                'getRegion',
                'getRegionId',
                'getRegionCode',
                'getCustomerId',
                'getParentId',
                'getData',
                '__wakeup',
            )
        );
        return $addressModelMock;
    }
}
