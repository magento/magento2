<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogImportExport\Test\Unit\Model\Import\Product\Validator;

use Magento\CatalogImportExport\Model\Import\Product\SkuProcessor;
use Magento\CatalogImportExport\Model\Import\Product\SkuStorage;
use Magento\CatalogImportExport\Model\Import\Product\Validator\SuperProductsSku;
use PHPUnit\Framework\MockObject\MockObject as Mock;
use PHPUnit\Framework\TestCase;

/**
 * Test for SuperProductsSku
 *
 * @see SuperProductsSku
 */
class SuperProductsSkuTest extends TestCase
{
    /**
     * @var SkuProcessor|Mock
     */
    private $skuProcessorMock;

    /**
     * @var SuperProductsSku
     */
    private $model;

    /**
     * @var SkuStorage|Mock
     */
    private SkuStorage $skuStorageMock;

    protected function setUp(): void
    {
        $this->skuProcessorMock = $this->getMockBuilder(SkuProcessor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->skuStorageMock = $this->createMock(SkuStorage::class);

        $this->model = new SuperProductsSku($this->skuProcessorMock, $this->skuStorageMock);
    }

    /**
     * @param array $value
     * @param array $oldSkus
     * @param bool $hasNewSku
     * @param bool $expectedResult
     * @dataProvider isValidDataProvider
     */
    public function testIsValid(array $value, array $oldSkus, $hasNewSku = false, $expectedResult = true)
    {
        $this->skuProcessorMock->expects($this->never())
            ->method('getOldSkus')
            ->willReturn($oldSkus);

        $this->skuStorageMock
            ->expects(!empty($value['_super_products_sku']) ? $this->once() : $this->never())
            ->method('has')
            ->willReturnCallback(function ($sku) use ($oldSkus) {
                return isset($oldSkus[strtolower($sku)]);
            });

        if ($hasNewSku) {
            $this->skuProcessorMock->expects($this->once())
                ->method('getNewSku')
                ->willReturn('someNewSku');
        }

        $this->assertEquals($expectedResult, $this->model->isValid($value));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return [
            [
                [],
                [],
            ],
            [
                [],
                ['sku1' => []]
            ],
            [
                ['_super_products_sku' => 'SKU1'],
                ['sku2' => []],
                false,
                false
            ],
            [
                ['_super_products_sku' => 'SKU1'],
                ['sku2' => []],
                true,
                true
            ],
            [
                ['_super_products_sku' => 'SKU1'],
                ['sku1' => []],
                false,
                true
            ],
        ];
    }
}
