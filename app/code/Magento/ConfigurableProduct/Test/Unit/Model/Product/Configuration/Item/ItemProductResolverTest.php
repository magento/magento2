<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Product\Configuration\Item;

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

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->parentProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->parentProduct
            ->method('getSku')
            ->willReturn('parent_product');

        $this->childProduct = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->childProduct
            ->method('getSku')
            ->willReturn('child_product');

        $this->option = $this->getMockBuilder(Option::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->option
            ->method('getProduct')
            ->willReturn($this->childProduct);

        $this->item = $this->getMockBuilder(ItemInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
     * @dataProvider provideScopeConfig
     * @param string $expectedSku
     * @param string $scopeValue
     * @param string | null $thumbnail
     */
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
