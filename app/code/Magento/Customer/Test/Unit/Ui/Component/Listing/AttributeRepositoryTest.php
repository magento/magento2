<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing;

use Magento\Customer\Ui\Component\Listing\AttributeRepository;

class AttributeRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Model\Resource\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeCollection;

    /** @var \Magento\Customer\Model\Resource\Address\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressAttributeCollection;

    /** @var \Magento\Customer\Model\Metadata\CustomerMetadata|\PHPUnit_Framework_MockObject_MockObject */
    protected $customerMetadata;

    /** @var \Magento\Customer\Model\Metadata\AddressMetadata|\PHPUnit_Framework_MockObject_MockObject */
    protected $addressMetadata;

    /** @var \Magento\Eav\Model\Entity\Attribute|\PHPUnit_Framework_MockObject_MockObject */
    protected $attribute;

    /** @var \Magento\Customer\Api\Data\AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $attributeMetadata;

    /** @var AttributeRepository */
    protected $component;

    public function setUp()
    {
        $this->attributeCollection = $this->getMock(
            'Magento\Customer\Model\Resource\Attribute\Collection',
            [],
            [],
            '',
            false
        );
        $this->addressAttributeCollection = $this->getMock(
            'Magento\Customer\Model\Resource\Address\Attribute\Collection',
            [],
            [],
            '',
            false
        );
        $this->customerMetadata = $this->getMock(
            'Magento\Customer\Model\Metadata\CustomerMetadata',
            [],
            [],
            '',
            false
        );
        $this->addressMetadata = $this->getMock(
            'Magento\Customer\Model\Metadata\AddressMetadata',
            [],
            [],
            '',
            false
        );
        $this->attribute = $this->getMock('Magento\Eav\Model\Entity\Attribute', [], [], '', false);
        $this->attributeMetadata = $this->getMockForAbstractClass(
            'Magento\Customer\Api\Data\AttributeMetadataInterface',
            [],
            '',
            false
        );

        $this->component = new AttributeRepository(
            $this->attributeCollection,
            $this->addressAttributeCollection,
            $this->customerMetadata,
            $this->addressMetadata
        );
    }

    public function testGetList()
    {
        $entityType = $this->getMock('Magento\Eav\Model\Entity\Type', [], [], '', false);
        $entityTypeAddress = 'customer_address';
        $attributeCode = 'attribute_code';
        $billingPrefix = 'billing_';

        $this->attributeCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([$this->attribute]);
        $this->addressAttributeCollection->expects($this->once())
            ->method('getItems')
            ->willReturn([]);
        $this->attribute->expects($this->atLeastOnce())
            ->method('getEntityType')
            ->willReturn($entityType);
        $entityType->expects($this->atLeastOnce())
            ->method('getEntityTypeCode')
            ->willReturn($entityTypeAddress);
        $this->addressMetadata->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($this->attributeMetadata);
        $this->attribute->expects($this->once())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);
        $this->attributeMetadata->expects($this->atLeastOnce())
            ->method('getAttributeCode')
            ->willReturn($attributeCode);

        $this->assertEquals(
            [$billingPrefix . $attributeCode => $this->attributeMetadata],
            $this->component->getList()
        );
    }

//    public function testGetMetadataByCode()
//    {
//        $entityType = $this->getMock('Magento\Eav\Model\Entity\Type', [], [], '', false);
//        $entityTypeAddress = 'customer';
//        $attributeCode = 'attribute_code';
//
//        $this->attributeCollection->expects($this->once())
//            ->method('getItems')
//            ->willReturn([$this->attribute]);
//        $this->addressAttributeCollection->expects($this->once())
//            ->method('getItems')
//            ->willReturn([]);
//        $this->attribute->expects($this->atLeastOnce())
//            ->method('getEntityType')
//            ->willReturn($entityType);
//        $entityType->expects($this->atLeastOnce())
//            ->method('getEntityTypeCode')
//            ->willReturn($entityTypeAddress);
//        $this->customerMetadata->expects($this->once())
//            ->method('getAttributeMetadata')
//            ->with($attributeCode)
//            ->willReturn($this->attributeMetadata);
//        $this->attribute->expects($this->once())
//            ->method('getAttributeCode')
//            ->willReturn($attributeCode);
//        $this->attributeMetadata->expects($this->atLeastOnce())
//            ->method('getAttributeCode')
//            ->willReturn($attributeCode);
//
//        $this->assertEquals($this->attributeMetadata, $this->component->getMetadataByCode($attributeCode));
//    }
}
