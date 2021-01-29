<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Test class for \Magento\Catalog\Model\Entity\Attribute\Set
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class AbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Get attribute list
     *
     * @return array
     */
    protected function _getAttributes()
    {
        $attributes = [];
        $codes = ['entity_type_id', 'attribute_set_id', 'created_at', 'updated_at', 'parent_id', 'increment_id'];
        foreach ($codes as $code) {
            $mock = $this->createPartialMock(
                \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
                ['isInSet', 'getApplyTo', 'getBackend', '__wakeup']
            );

            $mock->setAttributeId($code);
            $mock->setAttributeCode($code);

            $mock->expects($this->once())->method('isInSet')->willReturn(false);

            $attributes[$code] = $mock;
        }
        return $attributes;
    }

    public function testWalkAttributes()
    {
        $objectManager = new ObjectManager($this);

        $code = 'test_attr';
        $set = 10;
        $storeId = 100;

        $object = $this->createPartialMock(\Magento\Catalog\Model\Product::class, ['__wakeup']);

        $object->setData('test_attr', 'test_attr');
        $object->setData('attribute_set_id', $set);
        $object->setData('store_id', $storeId);

        $entityType = new \Magento\Framework\DataObject();
        $entityType->setEntityTypeCode('test');
        $entityType->setEntityTypeId(0);
        $entityType->setEntityTable('table');

        $attributes = $this->_getAttributes();

        $attribute = $this->createPartialMock(
            \Magento\Eav\Model\Entity\Attribute\AbstractAttribute::class,
            ['isInSet', 'getBackend', '__wakeup']
        );
        $attribute->setAttributeId($code);
        $attribute->setAttributeCode($code);

        $attribute->expects(
            $this->once()
        )->method(
            'isInSet'
        )->with(
            $this->equalTo($set)
        )->willReturn(
            false
        );

        $attributes[$code] = $attribute;

        /** @var $model \Magento\Catalog\Model\ResourceModel\AbstractResource */
        $arguments = $objectManager->getConstructArguments(
            \Magento\Catalog\Model\ResourceModel\AbstractResource::class
        );
        $model = $this->getMockBuilder(\Magento\Catalog\Model\ResourceModel\AbstractResource::class)
            ->setMethods(['getAttributesByCode'])
            ->setConstructorArgs($arguments)
            ->getMock();

        $model->expects($this->once())->method('getAttributesByCode')->willReturn($attributes);

        $model->walkAttributes('backend/afterSave', [$object]);
    }
}
