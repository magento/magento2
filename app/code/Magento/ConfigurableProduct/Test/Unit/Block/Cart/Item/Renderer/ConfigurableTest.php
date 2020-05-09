<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Helper\Product\Configuration;
use Magento\Catalog\Model\Product;
use Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable as Renderer;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\ConfigInterface;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigurableTest extends TestCase
{
    /** @var ConfigInterface|MockObject */
    private $configManager;

    /** @var Image|MockObject */
    private $imageHelper;

    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfig;

    /** @var MockObject */
    private $productConfigMock;

    /** @var Renderer */
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManagerHelper = new ObjectManager($this);
        $this->configManager = $this->getMockForAbstractClass(ConfigInterface::class);
        $this->imageHelper = $this->createPartialMock(
            Image::class,
            ['init', 'resize']
        );
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->productConfigMock = $this->createMock(Configuration::class);
        $this->renderer = $objectManagerHelper->getObject(
            \Magento\ConfigurableProduct\Block\Cart\Item\Renderer\Configurable::class,
            [
                'viewConfig' => $this->configManager,
                'imageHelper' => $this->imageHelper,
                'scopeConfig' => $this->scopeConfig,
                'productConfig' => $this->productConfigMock
            ]
        );
    }

    public function testGetOptionList()
    {
        $itemMock = $this->createMock(Item::class);
        $this->renderer->setItem($itemMock);
        $this->productConfigMock->expects($this->once())->method('getOptions')->with($itemMock);
        $this->renderer->getOptionList();
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(Product::class);
        $product->expects($this->exactly(2))->method('getIdentities')->willReturn($productTags);
        $item = $this->createMock(Item::class);
        $item->expects($this->exactly(2))->method('getProduct')->willReturn($product);
        $this->renderer->setItem($item);
        $this->assertEquals(array_merge($productTags, $productTags), $this->renderer->getIdentities());
    }
}
