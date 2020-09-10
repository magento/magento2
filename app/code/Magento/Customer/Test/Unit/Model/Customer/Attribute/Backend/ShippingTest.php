<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer\Attribute\Backend\Shipping;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class ShippingTest extends TestCase
{
    /**
     * @var Shipping
     */
    protected $testable;

    protected function setUp(): void
    {
        $logger = $this->getMockBuilder(LoggerInterface::class)
            ->getMock();
        /** @var LoggerInterface $logger */
        $this->testable = new Shipping($logger);
    }

    public function testBeforeSave()
    {
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultShipping', 'unsetDefaultShipping'])
            ->getMock();

        $object->expects($this->once())->method('getDefaultShipping')->willReturn(null);
        $object->expects($this->once())->method('unsetDefaultShipping')->willReturnSelf();
        /** @var DataObject $object */
        $this->testable->beforeSave($object);
    }

    public function testAfterSave()
    {
        $addressId = 1;
        $attributeCode = 'attribute_code';
        $defaultShipping = 'default Shipping address';
        $object = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultShipping', 'getAddresses', 'setDefaultShipping'])
            ->getMock();

        $address = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getPostIndex', 'getId'])
            ->getMock();

        $attribute = $this->getMockBuilder(AbstractAttribute::class)
            ->setMethods(['__wakeup', 'getEntity', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $entity = $this->getMockBuilder(AbstractEntity::class)
            ->setMethods(['saveAttribute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attribute->expects($this->once())->method('getEntity')->willReturn($entity);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $entity->expects($this->once())->method('saveAttribute')->with($this->logicalOr($object, $attributeCode));
        $address->expects($this->once())->method('getPostIndex')->willReturn($defaultShipping);
        $address->expects($this->once())->method('getId')->willReturn($addressId);
        $object->expects($this->once())->method('getDefaultShipping')->willReturn($defaultShipping);
        $object->expects($this->once())->method('setDefaultShipping')->with($addressId)->willReturnSelf();
        $object->expects($this->once())->method('getAddresses')->willReturn([$address]);
        /** @var \Magento\Framework\DataObject $object */
        /** @var AbstractAttribute $attribute */
        $this->testable->setAttribute($attribute);
        $this->testable->afterSave($object);
    }
}
