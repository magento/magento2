<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Test\Unit\Model\Plugin\Frontend;

use Magento\ConfigurableProduct\Model\Plugin\Frontend\ProductIdentitiesExtender;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;

/**
 * Class ProductIdentitiesExtenderTest
 */
class ProductIdentitiesExtenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|Configurable
     */
    private $configurableTypeMock;

    /**
     * @var ProductIdentitiesExtender
     */
    private $plugin;

    /** @var  MockObject|\Magento\Catalog\Model\Product */
    private $product;

    protected function setUp()
    {
        $this->product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId'])
            ->getMock();

        $this->configurableTypeMock = $this->getMockBuilder(Configurable::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->plugin = new ProductIdentitiesExtender($this->configurableTypeMock);
    }

    public function testAfterGetIdentities()
    {
        $identities = [
            'SomeCacheId',
            'AnotherCacheId',
        ];
        $productId = 12345;
        $childIdentities = [
            0 => [1, 2, 5, 100500]
        ];
        $expectedIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
            Product::CACHE_TAG . '_' . 1,
            Product::CACHE_TAG . '_' . 2,
            Product::CACHE_TAG . '_' . 5,
            Product::CACHE_TAG . '_' . 100500,
        ];

        $this->product->expects($this->once())
            ->method('getId')
            ->willReturn($productId);

        $this->configurableTypeMock->expects($this->once())
            ->method('getChildrenIds')
            ->with($productId)
            ->willReturn($childIdentities);

        $productIdentities = $this->plugin->afterGetIdentities($this->product, $identities);
        $this->assertEquals($expectedIdentities, $productIdentities);
    }
}
