<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Model\Customer\Attribute\Backend;

class ShippingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Shipping
     */
    protected $testable;

    public function setUp()
    {
        $logger = $this->getMockBuilder('Psr\Log\LoggerInterface')->getMock();
        /** @var \Psr\Log\LoggerInterface $logger */
        $this->testable = new Shipping($logger);
    }

    public function testBeforeSave()
    {
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultShipping', 'unsetDefaultShipping'])
            ->getMock();

        $object->expects($this->once())->method('getDefaultShipping')->will($this->returnValue(null));
        $object->expects($this->once())->method('unsetDefaultShipping')->will($this->returnSelf());
        /** @var \Magento\Framework\Object $object */

        $this->testable->beforeSave($object);
    }

    public function testAfterSave()
    {
        $addressId = 1;
        $attributeCode = 'attribute_code';
        $defaultShipping = 'default Shipping address';
        $object = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['getDefaultShipping', 'getAddresses', 'setDefaultShipping'])
            ->getMock();

        $address = $this->getMockBuilder('Magento\Framework\Object')
            ->disableOriginalConstructor()
            ->setMethods(['getPostIndex', 'getId'])
            ->getMock();

        $attribute = $this->getMockBuilder('Magento\Eav\Model\Entity\Attribute\AbstractAttribute')
            ->setMethods(['__wakeup', 'getEntity', 'getAttributeCode'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $entity = $this->getMockBuilder('Magento\Eav\Model\Entity\AbstractEntity')
            ->setMethods(['saveAttribute'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $attribute->expects($this->once())->method('getEntity')->will($this->returnValue($entity));
        $attribute->expects($this->once())->method('getAttributeCode')->will($this->returnValue($attributeCode));
        $entity->expects($this->once())->method('saveAttribute')->with($this->logicalOr($object, $attributeCode));
        $address->expects($this->once())->method('getPostIndex')->will($this->returnValue($defaultShipping));
        $address->expects($this->once())->method('getId')->will($this->returnValue($addressId));
        $object->expects($this->once())->method('getDefaultShipping')->will($this->returnValue($defaultShipping));
        $object->expects($this->once())->method('setDefaultShipping')->with($addressId)->will($this->returnSelf());
        $object->expects($this->once())->method('getAddresses')->will($this->returnValue([$address]));
        /** @var \Magento\Framework\Object $object */
        /** @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $attribute */

        $this->testable->setAttribute($attribute);
        $this->testable->afterSave($object);
    }
}
