<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Product;

class CatalogPriceTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Product\CatalogPrice
     */
    protected $model;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $priceFactoryMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $productMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $catalogPriceInterfaceMock;

    protected function setUp(): void
    {
        $this->priceFactoryMock = $this->createMock(\Magento\Catalog\Model\Product\CatalogPriceFactory::class);
        $this->productMock = $this->createMock(\Magento\Catalog\Model\Product::class);
        $this->catalogPriceInterfaceMock = $this->createMock(
            \Magento\Catalog\Model\Product\CatalogPriceInterface::class
        );
        $this->model = new \Magento\Catalog\Model\Product\CatalogPrice(
            $this->priceFactoryMock,
            ['custom_product_type' => 'CustomProduct/Model/CatalogPrice']
        );
    }

    public function testGetCatalogPriceWhenPoolContainsPriceModelForGivenProductType()
    {
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeId'
        )->willReturn(
            'custom_product_type'
        );
        $this->priceFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'CustomProduct/Model/CatalogPrice'
        )->willReturn(
            $this->catalogPriceInterfaceMock
        );
        $this->catalogPriceInterfaceMock->expects($this->once())->method('getCatalogPrice');
        $this->productMock->expects($this->never())->method('getFinalPrice');
        $this->model->getCatalogPrice($this->productMock);
    }

    public function testGetCatalogPriceWhenPoolDoesNotContainPriceModelForGivenProductType()
    {
        $this->productMock->expects($this->any())->method('getTypeId')->willReturn('test');
        $this->priceFactoryMock->expects($this->never())->method('create');
        $this->productMock->expects($this->once())->method('getFinalPrice');
        $this->catalogPriceInterfaceMock->expects($this->never())->method('getCatalogPrice');
        $this->model->getCatalogPrice($this->productMock);
    }

    public function testGetCatalogRegularPriceWhenPoolDoesNotContainPriceModelForGivenProductType()
    {
        $this->productMock->expects($this->any())->method('getTypeId')->willReturn('test');
        $this->priceFactoryMock->expects($this->never())->method('create');
        $this->catalogPriceInterfaceMock->expects($this->never())->method('getCatalogRegularPrice');
        $this->productMock->expects($this->once())->method('getPrice');
        $this->model->getCatalogRegularPrice($this->productMock);
    }

    public function testGetCatalogRegularPriceWhenPoolContainsPriceModelForGivenProductType()
    {
        $this->productMock->expects(
            $this->any()
        )->method(
            'getTypeId'
        )->willReturn(
            'custom_product_type'
        );
        $this->priceFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'CustomProduct/Model/CatalogPrice'
        )->willReturn(
            $this->catalogPriceInterfaceMock
        );
        $this->catalogPriceInterfaceMock->expects($this->once())->method('getCatalogRegularPrice');
        $this->productMock->expects($this->never())->method('getPrice');
        $this->model->getCatalogRegularPrice($this->productMock);
    }
}
