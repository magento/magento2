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
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\ScopeInterface;

/**
 * Tests \Magento\ConfigurableProduct\Model\Product\Configuration\Item\ItemProductResolver
 */
class ItemProductResolverTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ItemProductResolver
     */
    private $resolver;

    /**
     * @var ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManagerHelper = new ObjectManager($this);
        $this->scopeConfig = $this->createPartialMock(ScopeConfigInterface::class, ['getValue', 'isSetFlag']);
        $this->resolver = $objectManagerHelper->getObject(
            ItemProductResolver::class,
            ['scopeConfig' => $this->scopeConfig]
        );
    }

    /**
     * @param bool $existOption
     * @param string $configImageSource
     * @return void
     * @dataProvider getFinalProductDataProvider
     */
    public function testGetFinalProduct(bool $existOption, string $configImageSource)
    {
        $option = null;
        $parentProduct = $this->createMock(Product::class);
        $finalProduct = $parentProduct;

        if ($existOption) {
            $childProduct = $this->createPartialMock(Product::class, ['getData']);
            $childProduct->expects($this->once())->method('getData')->with('thumbnail')->willReturn('someImage');

            $option = $this->createPartialMock(
                OptionInterface::class,
                ['getProduct', 'getValue']
            );
            $option->expects($this->once())->method('getProduct')->willReturn($childProduct);

            $this->scopeConfig->expects($this->once())
                ->method('getValue')
                ->with(ItemProductResolver::CONFIG_THUMBNAIL_SOURCE, ScopeInterface::SCOPE_STORE)
                ->willReturn($configImageSource);

            $finalProduct = ($configImageSource == Thumbnail::OPTION_USE_PARENT_IMAGE) ? $parentProduct : $childProduct;
        }

        $item = $this->createPartialMock(
            ItemInterface::class,
            ['getProduct', 'getOptionByCode', 'getFileDownloadParams']
        );
        $item->expects($this->exactly(2))->method('getProduct')->willReturn($parentProduct);
        $item->expects($this->once())->method('getOptionByCode')->with('simple_product')->willReturn($option);

        $this->assertEquals($finalProduct, $this->resolver->getFinalProduct($item));
    }

    /**
     * @return array
     */
    public function getFinalProductDataProvider(): array
    {
        return [
            [false, Thumbnail::OPTION_USE_PARENT_IMAGE],
            [true, Thumbnail::OPTION_USE_PARENT_IMAGE],
            [true, Thumbnail::OPTION_USE_OWN_IMAGE],
        ];
    }
}
