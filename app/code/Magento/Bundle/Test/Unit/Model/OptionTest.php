<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OptionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectionFirst;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $selectionSecond;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Bundle\Model\Option
     */
    protected $model;

    protected function setUp(): void
    {
        $this->selectionFirst = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeup', 'isSaleable', 'getIsDefault', 'getSelectionId']
        );
        $this->selectionSecond = $this->createPartialMock(
            \Magento\Catalog\Model\Product::class,
            ['__wakeup', 'isSaleable', 'getIsDefault', 'getSelectionId']
        );
        $this->resource = $this->createPartialMock(\Magento\Framework\Model\ResourceModel\AbstractResource::class, [
                '_construct',
                'getConnection',
                'getIdFieldName',
                'getSearchableData',
            ]);
        $this->model = (new ObjectManager($this))->getObject(\Magento\Bundle\Model\Option::class, [
            'resource' => $this->resource,
        ]);
    }

    /**
     * @covers \Magento\Bundle\Model\Option::addSelection
     */
    public function testAddSelection()
    {
        $this->model->addSelection($this->selectionFirst);

        $this->assertContains($this->selectionFirst, $this->model->getSelections());
    }

    public function testIsSaleablePositive()
    {
        $this->selectionFirst->expects($this->any())->method('isSaleable')->willReturn(true);
        $this->selectionSecond->expects($this->any())->method('isSaleable')->willReturn(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertTrue($this->model->isSaleable());
    }

    public function testIsSaleableNegative()
    {
        $this->selectionFirst->expects($this->any())->method('isSaleable')->willReturn(false);
        $this->selectionSecond->expects($this->any())->method('isSaleable')->willReturn(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertFalse($this->model->isSaleable());
    }

    public function testGetDefaultSelection()
    {
        $this->selectionFirst->expects($this->any())->method('getIsDefault')->willReturn(true);
        $this->selectionSecond->expects($this->any())->method('getIsDefault')->willReturn(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertEquals($this->selectionFirst, $this->model->getDefaultSelection());
    }

    public function testGetDefaultSelectionNegative()
    {
        $this->selectionFirst->expects($this->any())->method('getIsDefault')->willReturn(false);
        $this->selectionSecond->expects($this->any())->method('getIsDefault')->willReturn(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertNull($this->model->getDefaultSelection());
    }

    /**
     * @param string $type
     * @param bool $expectedValue
     * @dataProvider dataProviderForIsMultiSelection
     */
    public function testIsMultiSelection($type, $expectedValue)
    {
        $this->model->setType($type);

        $this->assertEquals($expectedValue, $this->model->isMultiSelection());
    }

    /**
     * @return array
     */
    public function dataProviderForIsMultiSelection()
    {
        return [
            ['checkbox', true],
            ['multi', true],
            ['some_type', false],
        ];
    }

    public function testGetSearchableData()
    {
        $productId = 15;
        $storeId = 1;
        $data = 'data';

        $this->resource->expects($this->any())->method('getSearchableData')->with($productId, $storeId)
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getSearchableData($productId, $storeId));
    }

    public function testGetSelectionById()
    {
        $selectionId = 15;

        $this->selectionFirst->expects($this->any())->method('getSelectionId')->willReturn($selectionId);
        $this->selectionSecond->expects($this->any())->method('getSelectionId')->willReturn(16);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertEquals($this->selectionFirst, $this->model->getSelectionById($selectionId));
    }

    public function testGetSelectionByIdNegative()
    {
        $selectionId = 15;

        $this->selectionFirst->expects($this->any())->method('getSelectionId')->willReturn(16);
        $this->selectionSecond->expects($this->any())->method('getSelectionId')->willReturn(17);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertNull($this->model->getSelectionById($selectionId));
    }
}
