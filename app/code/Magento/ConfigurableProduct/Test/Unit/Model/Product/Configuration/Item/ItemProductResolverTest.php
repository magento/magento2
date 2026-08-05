<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Configuration\Item;

use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Catalog\Model\Config\Source\Product\Thumbnail;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Model\Product\Configuration\Item\Option\OptionInterface;
use Magento\ConfigurableProduct\Model\Product\Configuration\Item\ItemProductResolver;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Item\Option;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ItemProductResolverTest extends TestCase
{
    /** @var ItemProductResolver */
    private $model;
    /** @var ItemInterface | MockObject */
    private $item;
    /** @var Product | MockObject */
    private $parentProduct;
    /** @var  ScopeConfigInterface | MockObject */
    private $scopeConfig;
    /** @var OptionInterface | MockObject */
    private $option;
    /** @var Product | MockObject */
    private $childProduct;

    /**
     * Set up method
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->scopeConfig = $this->createMock(ScopeConfigInterface::class);

        $this->parentProduct = $this->createMock(Product::class);
        $this->parentProduct
            ->method('getSku')
            ->willReturn('parent_product');

        $this->childProduct = $this->createMock(Product::class);
        $this->childProduct
            ->method('getSku')
            ->willReturn('child_product');

        $this->option = $this->createMock(Option::class);

        $this->option
            ->method('getProduct')
            ->willReturn($this->childProduct);

        $this->item = $this->createMock(ItemInterface::class);

        $this->item
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->parentProduct);

        $this->model = new ItemProductResolver($this->scopeConfig);
    }

    /**
     * Test for deleted child product from configurable product
     */
    public function testGetFinalProductChildIsNull(): void
    {
        $this->scopeConfig->expects($this->never())->method('getValue');
        $this->childProduct->expects($this->never())->method('getData');

        $this->item->expects($this->once())
            ->method('getOptionByCode')
            ->willReturn(null);

        $finalProduct = $this->model->getFinalProduct($this->item);
        $this->assertEquals(
            $this->parentProduct->getSku(),
            $finalProduct->getSku()
        );
    }

    /**
     * Tests child product from configurable product
     *
     * @param string $expectedSku
     * @param string $scopeValue
     * @param string | null $thumbnail
     */
    #[DataProvider('provideScopeConfig')]
    public function testGetFinalProductChild($expectedSku, $scopeValue, $thumbnail): void
    {
        $this->item->expects($this->once())
            ->method('getOptionByCode')
            ->willReturn($this->option);

        $this->childProduct
            ->expects($this->once())
            ->method('getData')
            ->willReturn($thumbnail);

        $this->scopeConfig->expects($this->once())
            ->method('getValue')
            ->willReturn($scopeValue);

        $finalProduct = $this->model->getFinalProduct($this->item);
        $this->assertEquals($expectedSku, $finalProduct->getSku());
    }

    /**
     * Dataprovider for scope test
     * @return array
     */
    public static function provideScopeConfig(): array
    {
        return [
            ['child_product', Thumbnail::OPTION_USE_OWN_IMAGE, 'thumbnail'],
            ['parent_product', Thumbnail::OPTION_USE_PARENT_IMAGE, 'thumbnail'],

            ['parent_product', Thumbnail::OPTION_USE_OWN_IMAGE, null],
            ['parent_product', Thumbnail::OPTION_USE_OWN_IMAGE, 'no_selection'],

            ['parent_product', Thumbnail::OPTION_USE_PARENT_IMAGE, null],
            ['parent_product', Thumbnail::OPTION_USE_PARENT_IMAGE, 'no_selection'],
        ];
    }
}
