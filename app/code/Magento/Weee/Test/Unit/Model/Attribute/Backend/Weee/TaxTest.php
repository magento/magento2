<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * Test class for \Magento\Weee\Model\Attribute\Backend\Weee\Tax
 */
namespace Magento\Weee\Test\Unit\Model\Attribute\Backend\Weee;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Attribute;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Weee\Model\Attribute\Backend\Weee\Tax;
use Magento\Weee\Model\ResourceModel\Attribute\Backend\Weee\Tax as AttributeTaxResource;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TaxTest extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetBackendModelName()
    {
        $this->assertEquals(
            Tax::class,
            Tax::getBackendModelName()
        );
    }

    /**
     * @param $data
     * @param $expected
     */
    #[DataProvider('dataProviderValidate')]
    public function testValidate($data, $expected)
    {
        $attributeMock = $this->createPartialMock(Attribute::class, ['getName']);
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->willReturn('weeeTax');

        $modelMock = $this->createPartialMock(Tax::class, ['getAttribute']);
        $modelMock
            ->expects($this->any())
            ->method('getAttribute')
            ->willReturn($attributeMock);

        $taxes = [reset($data)];
        $productMock = $this->createPartialMock(Product::class, ['getData']);
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn($taxes);

        // No exception
        $modelMock->validate($productMock);

        $taxes = $data;
        $productMock = $this->createPartialMock(Product::class, ['getData']);
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn($taxes);

        // Exception caught
        $this->expectException('Exception');
        $this->expectExceptionMessage($expected);
        $modelMock->validate($productMock);
    }

    /**
     * @return array
     */
    public static function dataProviderValidate()
    {
        return [
            'withDuplicate' => [
                'data' => [
                    ['state' => 12, 'country' => 'US', 'website_id' => '1'],
                    ['state' => 99, 'country' => 'ES', 'website_id' => '1'],
                    ['state' => 12, 'country' => 'US', 'website_id' => '1'],
                    ['state' => null, 'country' => 'ES', 'website_id' => '1']
                ],
                'expected' => 'Set unique country-state combinations within the same fixed product tax. '
                    . 'Verify the combinations and try again.',
            ]
        ];
    }

    public function testAfterLoad()
    {
        $data = [['website_id' => 1, 'value' => 1]];

        $attributeTaxMock = $this->createPartialMock(AttributeTaxResource::class, ['loadProductData']);
        $attributeTaxMock
            ->expects($this->any())
            ->method('loadProductData')
            ->willReturn($data);

        $attributeMock = $this->createPartialMock(Attribute::class, ['getName']);
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->willReturn('weeeTax');

        $model = $this->objectManager->getObject(
            Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
                '_attribute' => $attributeMock
            ]
        );

        $model->setAttribute($attributeMock);
        $productMock = $this->createPartialMock(Product::class, ['setData']);

        $result = $model->afterLoad($productMock);
        $this->assertNotNull($result);
    }

    /**
     * Tests the specific method with various regions
     *
     * @param array $origData
     * @param array $currentData
     * @param array $expectedData
     */
    #[DataProvider('dataProviderAfterSaveWithRegion')]
    public function testAfterSaveWithRegion($origData, $currentData, $expectedData)
    {
        $productMock = $this->createPartialMock(Product::class, ['getOrigData', 'getData']);

        $productMock
            ->expects($this->once())
            ->method('getOrigData')
            ->willReturn($origData);
        $productMock
            ->expects($this->any())
            ->method('getData')
            ->willReturn($currentData);

        $attributeTaxMock = $this->createPartialMock(
            AttributeTaxResource::class,
            ['deleteProductData', 'insertProductData']
        );
        $attributeTaxMock
            ->expects($this->once())
            ->method('deleteProductData')
            ->willReturn(null);
        $attributeTaxMock
            ->expects($this->once())
            ->method('insertProductData')
            ->with($productMock, $expectedData)
            ->willReturn(null);

        $attributeMock = $this->createPartialMock(Attribute::class, ['getName', 'getId']);
        $attributeMock
            ->expects($this->any())
            ->method('getName')
            ->willReturn('weeeTax');
        $attributeMock
            ->expects($this->any())
            ->method('getId')
            ->willReturn(1);

        $model = $this->objectManager->getObject(
            Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
                '_attribute' => $attributeMock
            ]
        );

        $model->setAttribute($attributeMock);
        $model->afterSave($productMock);
    }

    /**
     * @return array
     */
    public static function dataProviderAfterSaveWithRegion()
    {
        return [
            'withRegion' => [
                'origData' => [['state' => 12, 'country' => 'US', 'website_id' => '1']],
                'currentData' => [['state' => 12, 'country' => 'US', 'website_id' => '2', 'price' => 100]],
                'expectedData' => ['state' => 12, 'country' => 'US', 'website_id' => '2', 'value' => 100,
                    'attribute_id' => 1]],
            'withNoRegion' => [
                'origData' => [['country' => 'US', 'website_id' => '1']],
                'currentData' => [['country' => 'US', 'website_id' => '2', 'price' => 100]],
                'expectedData' => ['state' => 0, 'country' => 'US', 'website_id' => '2', 'value' => 100,
                    'attribute_id' => 1]]
        ];
    }

    public function testAfterDelete()
    {
        $attributeTaxMock = $this->createPartialMock(AttributeTaxResource::class, ['deleteProductData']);
        $attributeTaxMock
            ->expects($this->once())
            ->method('deleteProductData')
            ->with(null, null)
            ->willReturn(null);

        $model = $this->objectManager->getObject(
            Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->afterDelete(null);
    }

    public function testGetTable()
    {
        $attributeTaxMock = $this->createPartialMock(AttributeTaxResource::class, ['getTable']);
        $attributeTaxMock
            ->expects($this->once())
            ->method('getTable')
            ->with('weee_tax')
            ->willReturn(null);

        $model = $this->objectManager->getObject(
            Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->getTable();
    }

    /**
     * Test method GetEntityIdField.
     *
     * @return void
     */
    public function testGetEntityIdField() : void
    {
        $attributeTaxMock = $this->createPartialMock(AttributeTaxResource::class, ['getIdFieldName']);

        $attributeTaxMock
            ->expects($this->once())
            ->method('getIdFieldName')
            ->willReturn(null);

        $model = $this->objectManager->getObject(
            Tax::class,
            [
                'attributeTax' => $attributeTaxMock,
            ]
        );

        $model->getEntityIdField();
    }
}
