<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Test\Unit\Block\Cart\Item\Renderer;

use Magento\Catalog\Model\Config\Source\Product\Thumbnail as ThumbnailSource;
use Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped as Renderer;

class GroupedTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $scopeConfig;

    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->scopeConfig = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $this->renderer = $objectManagerHelper->getObject(
            \Magento\GroupedProduct\Block\Cart\Item\Renderer\Grouped::class,
            ['scopeConfig' => $this->scopeConfig]
        );
    }

    public function testGetIdentities()
    {
        $productTags = ['catalog_product_1'];
        $product = $this->createMock(\Magento\Catalog\Model\Product::class);
        $product->expects($this->exactly(2))->method('getIdentities')->will($this->returnValue($productTags));
        $item = $this->createMock(\Magento\Quote\Model\Quote\Item::class);
        $item->expects($this->exactly(2))->method('getProduct')->will($this->returnValue($product));
        $this->renderer->setItem($item);
        $this->assertEquals(array_merge($productTags, $productTags), $this->renderer->getIdentities());
    }
}
