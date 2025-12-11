<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Customer\Attribute\Backend;

use Magento\Customer\Model\Customer\Attribute\Backend\Billing;
use Magento\Eav\Model\Entity\AbstractEntity;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\DataObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class BillingTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var Billing
     */
    protected $testable;

    protected function setUp(): void
    {
        $this->testable = new Billing();
    }

    public function testBeforeSave()
    {
        $object = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getDefaultBilling', 'unsetDefaultBilling']
        );

        $object->expects($this->once())->method('getDefaultBilling')->willReturn(null);
        $object->expects($this->once())->method('unsetDefaultBilling')->willReturnSelf();
        /** @var DataObject $object */
        $this->testable->beforeSave($object);
    }

    public function testAfterSave()
    {
        $addressId = 1;
        $attributeCode = 'attribute_code';
        $defaultBilling = 'default billing address';
        $object = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getDefaultBilling', 'getAddresses', 'setDefaultBilling']
        );

        $address = $this->createPartialMockWithReflection(
            DataObject::class,
            ['getPostIndex', 'getId']
        );

        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            [
                '__wakeup',
                'getEntity',
                'getAttributeCode'
            ]
        );

        $entity = $this->createPartialMock(
            AbstractEntity::class,
            [
                'saveAttribute'
            ]
        );

        $attribute->expects($this->once())->method('getEntity')->willReturn($entity);
        $attribute->expects($this->once())->method('getAttributeCode')->willReturn($attributeCode);
        $entity->expects($this->once())->method('saveAttribute')->with($this->logicalOr($object, $attributeCode));
        $address->expects($this->once())->method('getPostIndex')->willReturn($defaultBilling);
        $address->expects($this->once())->method('getId')->willReturn($addressId);
        $object->expects($this->once())->method('getDefaultBilling')->willReturn($defaultBilling);
        $object->expects($this->once())->method('setDefaultBilling')->with($addressId)->willReturnSelf();
        $object->expects($this->once())->method('getAddresses')->willReturn([$address]);
        /** @var \Magento\Framework\DataObject $object */
        /** @var AbstractAttribute $attribute */
        $this->testable->setAttribute($attribute);
        $this->testable->afterSave($object);
    }
}
