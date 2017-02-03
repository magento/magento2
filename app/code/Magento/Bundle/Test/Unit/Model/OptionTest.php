<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Bundle\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class OptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionFirst;

    /**
     * @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $selectionSecond;

    /**
     * @var \Magento\Framework\Model\ResourceModel\AbstractResource|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resource;

    /**
     * @var \Magento\Bundle\Model\Option
     */
    protected $model;

    protected function setUp()
    {
        $this->selectionFirst = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'isSaleable', 'getIsDefault', 'getSelectionId'],
            [],
            '',
            false
        );
        $this->selectionSecond = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['__wakeup', 'isSaleable', 'getIsDefault', 'getSelectionId'],
            [],
            '',
            false
        );
        $this->resource = $this->getMock(
            'Magento\Framework\Model\ResourceModel\AbstractResource',
            [
                '_construct',
                'getConnection',
                'getIdFieldName',
                'getSearchableData',
            ],
            [],
            '',
            false
        );
        $this->model = (new ObjectManager($this))->getObject('Magento\Bundle\Model\Option', [
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
        $this->selectionFirst->expects($this->any())->method('isSaleable')->will($this->returnValue(true));
        $this->selectionSecond->expects($this->any())->method('isSaleable')->will($this->returnValue(false));

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertTrue($this->model->isSaleable());
    }

    public function testIsSaleableNegative()
    {
        $this->selectionFirst->expects($this->any())->method('isSaleable')->will($this->returnValue(false));
        $this->selectionSecond->expects($this->any())->method('isSaleable')->will($this->returnValue(false));

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertFalse($this->model->isSaleable());
    }

    public function testGetDefaultSelection()
    {
        $this->selectionFirst->expects($this->any())->method('getIsDefault')->will($this->returnValue(true));
        $this->selectionSecond->expects($this->any())->method('getIsDefault')->will($this->returnValue(false));

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertEquals($this->selectionFirst, $this->model->getDefaultSelection());
    }

    public function testGetDefaultSelectionNegative()
    {
        $this->selectionFirst->expects($this->any())->method('getIsDefault')->will($this->returnValue(false));
        $this->selectionSecond->expects($this->any())->method('getIsDefault')->will($this->returnValue(false));

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
            ->will($this->returnValue($data));

        $this->assertEquals($data, $this->model->getSearchableData($productId, $storeId));
    }

    public function testGetSelectionById()
    {
        $selectionId = 15;

        $this->selectionFirst->expects($this->any())->method('getSelectionId')->will($this->returnValue($selectionId));
        $this->selectionSecond->expects($this->any())->method('getSelectionId')->will($this->returnValue(16));

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertEquals($this->selectionFirst, $this->model->getSelectionById($selectionId));
    }

    public function testGetSelectionByIdNegative()
    {
        $selectionId = 15;

        $this->selectionFirst->expects($this->any())->method('getSelectionId')->will($this->returnValue(16));
        $this->selectionSecond->expects($this->any())->method('getSelectionId')->will($this->returnValue(17));

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertNull($this->model->getSelectionById($selectionId));
    }
}
