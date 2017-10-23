<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Test\Unit\Model\Adapter\Mysql\Field;

use Magento\Framework\Search\Adapter\Mysql\Field\FieldInterface;

/**
 * Unit tests for Magento\CatalogSearch\Model\Adapter\Mysql\Field\Resolver class.
 */
class ResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeCollection;

    /**
     * @var \Magento\Framework\Search\Adapter\Mysql\Field\FieldFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fieldFactory;

    /**
     * @var \Magento\CatalogSearch\Model\Adapter\Mysql\Field\Resolver
     */
    private $model;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->attributeCollection = $this->getMockBuilder(
            \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection::class
        )
            ->disableOriginalConstructor()
            ->getMock();
        $this->fieldFactory = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\Field\FieldFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $objectManagerHelper->getObject(
            \Magento\CatalogSearch\Model\Adapter\Mysql\Field\Resolver::class,
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
     * @param \PHPUnit_Framework_MockObject_MockObject $field
     * @param array $expectedResult
     * @return void
     * @dataProvider resolveDataProvider
     */
    public function testResolve(
        array $fields,
        $attributeId,
        \PHPUnit_Framework_MockObject_MockObject $field,
        array $expectedResult
    ) {
        $item = $this->getMockBuilder(\Magento\Framework\DataObject::class)
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
        $field = $this->getMockBuilder(\Magento\Framework\Search\Adapter\Mysql\Field\Field::class)
            ->disableOriginalConstructor()
            ->getMock();
        return [
          [['code_1'], 1, $field, ['code_1' => $field]],
          [['*'], null, $field, [$field]],
        ];
    }
}
