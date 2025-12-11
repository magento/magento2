<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Bundle\Test\Unit\Model;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Bundle\Model\Option;
use Magento\Bundle\Model\ResourceModel\Option as OptionResource;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Test\Unit\Helper\ProductTestHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(\Magento\Bundle\Model\Option::class)]
class OptionTest extends TestCase
{
    /**
     * @var ProductTestHelper
     */
    protected $selectionFirst;

    /**
     * @var ProductTestHelper
     */
    protected $selectionSecond;

    /**
     * @var OptionResource|MockObject
     */
    protected $resource;

    /**
     * @var Option
     */
    protected $model;

    protected function setUp(): void
    {
        $this->selectionFirst = new ProductTestHelper();
        $this->selectionSecond = new ProductTestHelper();
        $this->resource = $this->createPartialMock(
            OptionResource::class,
            ['getSearchableData', 'getConnection', 'getIdFieldName', '_construct']
        );
        $this->resource->method('getIdFieldName')->willReturn('option_id');
        $this->model = (new ObjectManager($this))->getObject(Option::class, [
            'resource' => $this->resource,
        ]);
    }

    public function testAddSelection()
    {
        $this->model->addSelection($this->selectionFirst);

        $this->assertContains($this->selectionFirst, $this->model->getSelections());
    }

    public function testIsSaleablePositive()
    {
        $this->selectionFirst->setIsSaleable(true);
        $this->selectionSecond->setIsSaleable(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertTrue($this->model->isSaleable());
    }

    public function testIsSaleableNegative()
    {
        $this->selectionFirst->setIsSaleable(false);
        $this->selectionSecond->setIsSaleable(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertFalse($this->model->isSaleable());
    }

    public function testGetDefaultSelection()
    {
        $this->selectionFirst->setIsDefault(true);
        $this->selectionSecond->setIsDefault(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertEquals($this->selectionFirst, $this->model->getDefaultSelection());
    }

    public function testGetDefaultSelectionNegative()
    {
        $this->selectionFirst->setIsDefault(false);
        $this->selectionSecond->setIsDefault(false);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertNull($this->model->getDefaultSelection());
    }

    /**
     * @param string $type
     * @param bool $expectedValue
     */
    #[DataProvider('dataProviderForIsMultiSelection')]
    public function testIsMultiSelection($type, $expectedValue)
    {
        $this->model->setType($type);

        $this->assertEquals($expectedValue, $this->model->isMultiSelection());
    }

    /**
     * @return array
     */
    public static function dataProviderForIsMultiSelection()
    {
        return [
            ['checkbox', true],
            ['multi', true],
            ['some_type', false],
        ];
    }

    /**
     * @return void
     */
    public function testGetSearchableData()
    {
        $productId = 15;
        $storeId = 1;
        $data = 'data';

        $this->resource->expects($this->any())->method('getSearchableData')->with($productId, $storeId)
            ->willReturn($data);

        $this->assertEquals($data, $this->model->getSearchableData($productId, $storeId));
    }

    /**
     * @return void
     */
    public function testGetSelectionById()
    {
        $selectionId = 15;

        $this->selectionFirst->setSelectionId($selectionId);
        $this->selectionSecond->setSelectionId(16);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertEquals($this->selectionFirst, $this->model->getSelectionById($selectionId));
    }

    /**
     * @return void
     */
    public function testGetSelectionByIdNegative()
    {
        $selectionId = 15;

        $this->selectionFirst->setSelectionId(16);
        $this->selectionSecond->setSelectionId(17);

        $this->model->setSelections([$this->selectionFirst, $this->selectionSecond]);
        $this->assertNull($this->model->getSelectionById($selectionId));
    }
}
