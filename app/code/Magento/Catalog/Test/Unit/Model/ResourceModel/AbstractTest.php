<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Catalog\Model\Entity\Attribute\Set
 */
namespace Magento\Catalog\Test\Unit\Model\ResourceModel;

use Magento\Catalog\Model\Factory;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\AbstractResource;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Eav\Model\Entity\Attribute\Set;
use Magento\Eav\Model\Entity\Attribute\UniqueValidationInterface;
use Magento\Eav\Model\Entity\Context;
use Magento\Framework\DataObject;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AbstractTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private $context;

    /**
     * @var StoreManagerInterface|MockObject
     */
    private $storeManager;

    /**
     * @var Factory|MockObject
     */
    private $modelFactory;

    /**
     * @var UniqueValidationInterface|MockObject
     */
    private $uniqueValidator;

    /**
     * @var AbstractResource|MockObject
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->context = $this->createMock(Context::class);
        $this->storeManager = $this->createMock(StoreManagerInterface::class);
        $this->modelFactory = $this->createMock(Factory::class);
        $this->uniqueValidator = $this->createMock(UniqueValidationInterface::class);
    }

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
            $mock = $this->getMockBuilder(AbstractAttribute::class)
                ->addMethods(['getApplyTo'])
                ->onlyMethods(['isInSet', 'getBackend'])
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();

            $mock->setAttributeId($code);
            $mock->setAttributeCode($code);

            $mock->expects($this->once())->method('isInSet')->willReturn(false);

            $attributes[$code] = $mock;
        }
        return $attributes;
    }

    public function testWalkAttributes()
    {
        $code = 'test_attr';
        $set = 10;
        $storeId = 100;

        $object = $this->createPartialMock(Product::class, ['__wakeup']);

        $object->setData('test_attr', 'test_attr');
        $object->setData('attribute_set_id', $set);
        $object->setData('store_id', $storeId);

        $entityType = new DataObject();
        $entityType->setEntityTypeCode('test');
        $entityType->setEntityTypeId(0);
        $entityType->setEntityTable('table');

        $attributes = $this->_getAttributes();

        $attribute = $this->createPartialMock(
            AbstractAttribute::class,
            ['isInSet', 'getBackend']
        );
        $attribute->setAttributeId($code);
        $attribute->setAttributeCode($code);

        $attribute->expects(
            $this->once()
        )->method(
            'isInSet'
        )->with(
            $set
        )->willReturn(
            false
        );

        $attributes[$code] = $attribute;

        $attrSetEntity = $this->createMock(Set::class);
        $this->context->method('getAttributeSetEntity')
            ->willReturn($attrSetEntity);
        $attrSetEntity->expects($this->once())
            ->method('addSetInfo')
            ->with($entityType, $attributes, $set);

        $this->model = $this->getMockBuilder(AbstractResource::class)
            ->setConstructorArgs(
                [
                    $this->context,
                    $this->storeManager,
                    $this->modelFactory,
                    [],
                    $this->uniqueValidator
                ]
            )
            ->onlyMethods(['getAttributesByCode', 'getEntityType'])
            ->getMockForAbstractClass();

        $this->model->expects($this->once())
            ->method('getAttributesByCode')
            ->willReturn($attributes);
        $this->model->expects($this->once())
            ->method('getEntityType')
            ->willReturn($entityType);

        $this->model->walkAttributes('backend/afterSave', [$object]);
    }
}
