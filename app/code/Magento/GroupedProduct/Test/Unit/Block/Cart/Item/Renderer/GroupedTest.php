<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped as Renderer;
use Magento\Quote\Model\Quote\Item;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GroupedTest extends TestCase
{
    /** @var ScopeConfigInterface|MockObject */
    private $scopeConfig;

    /** @var Renderer */
    private $renderer;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManagerHelper = new ObjectManager($this);
        $this->scopeConfig = $this->getMockForAbstractClass(ScopeConfigInterface::class);
        $this->renderer = $objectManagerHelper->getObject(
            \Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped::class,
            ['scopeConfig' => $this->scopeConfig]
        );
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
