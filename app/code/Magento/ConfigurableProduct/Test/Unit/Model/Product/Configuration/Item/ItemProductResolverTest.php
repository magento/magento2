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
use PHPUnit\Framework\TestCase;

/**
 * ItemProductResolver test
 */
class ItemProductResolverTest extends TestCase
{
    /**
     * @var ItemProductResolver
     */
    private $model;

    /**
     * @var ItemInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $item;

    /**
     * @var Product | \PHPUnit_Framework_MockObject_MockObject
     */
    private $parentProduct;

    /**
     * @var  ScopeConfigInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var OptionInterface | \PHPUnit_Framework_MockObject_MockObject
     */
    private $option;

    /**
     * @var Product | \PHPUnit_Framework_MockObject_MockObject
     */
    private $childProduct;

    /**
     * Set up method
     *
     * @return void
     */
    protected function setUp()
    {
        parent::setUp();

        $this->scopeConfig = $this->getMockBuilder(ScopeConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

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
            ->getMock();

        $this->item
            ->expects($this->once())
            ->method('getProduct')
            ->willReturn($this->parentProduct);

        $this->model = new ItemProductResolver($this->scopeConfig);
    }

    /**
     * Test for deleted child product from configurable product
     *
     * @return void
     */
    public function testGetFinalProductChildIsNull()
    {
        $this->item->method('getOptionByCode')
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
     * @return void
     */
    public function testGetFinalProductChild($expectedSku, $scopeValue, $thumbnail)
    {
        $this->item->method('getOptionByCode')
            ->willReturn($this->option);

        $this->childProduct->method('getData')
            ->willReturn($thumbnail);

        $this->scopeConfig->method('getValue')
            ->willReturn($scopeValue);

        $finalProduct = $this->model->getFinalProduct($this->item);
        $this->assertEquals($expectedSku, $finalProduct->getSku());
    }

    /**
     * Data provider for scope test
     *
     * @return array
     */
    public function provideScopeConfig(): array
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
