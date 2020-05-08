<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Field;

use Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection;
use Magento\CatalogSearch\Model\Adapter\Mysql\Field\Resolver;
use Magento\Framework\DataObject;
use Magento\Framework\Search\Adapter\Mysql\Field\Field;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldFactory;
use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for Magento\CatalogSearch\Model\Adapter\Mysql\Field\Resolver class.
 *
 * @deprecated Implementation class was replaced
 * @see \Magento\ElasticSearch
 */
class ResolverTest extends TestCase
{
    /**
     * @var Collection|MockObject
     */
    private $attributeCollection;

    /**
     * @var FieldFactory|MockObject
     */
    private $fieldFactory;

    /**
     * @var Resolver
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->attributeCollection = $this->getMockBuilder(
            Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldFactory = $this->getMockBuilder(FieldFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            Resolver::class,
            [
                'attributeCollection' => $this->attributeCollection,
                'fieldFactory' => $this->fieldFactory,
            ]
        );
    }

    /**
     * Test resolve method.
     *
     * @param array $fields
     * @param int|null $attributeId
     * @param MockObject $field
     * @param array $expectedResult
     * @return void
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        array $fields,
        $attributeId,
        MockObject $field,
        array $expectedResult
    ) {
        $item = $this->getMockBuilder(DataObject::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();
        $this->attributeCollection->expects($this->once())
            ->method('addFieldToFilter')
            ->with('attribute_code', ['in' => $fields])
            ->willReturnSelf();
        $this->fieldFactory->expects($this->once())
            ->method('create')
            ->with(['attributeId' => $attributeId, 'column' => 'data_index', 'type' => FieldInterface::TYPE_FULLTEXT])
            ->willReturn($field);
        if ($attributeId) {
            $this->attributeCollection->expects($this->once())
                ->method('getItemByColumnValue')
                ->with('attribute_code', $fields[0])
                ->willReturn($item);
            $item->expects($this->once())->method('getId')->willReturn($attributeId);
        }

        $this->assertSame($expectedResult, $this->model->resolve($fields));
    }

    /**
     * Data provider for resolve method.
     *
     * @return array
     */
    public function resolveDataProvider()
    {
        $field = $this->getMockBuilder(Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        return [
            [['code_1'], 1, $field, ['code_1' => $field]],
            [['*'], null, $field, [$field]],
        ];
    }
}
