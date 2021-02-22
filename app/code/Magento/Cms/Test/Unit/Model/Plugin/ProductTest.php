<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Cms\Model\Page;
use Magento\Cms\Model\Plugin\Product;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject as MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Product plugin test
 */
class ProductTest extends TestCase
{
    /**
     * @var Product
     */
    private $plugin;

    /**
     * @var MockObject|CatalogProduct
     */
    private $product;

    /**
     * @var MockObject|Page
     */
    private $page;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $this->product = $this->getMockBuilder(CatalogProduct::class)
            ->disableOriginalConstructor()
            ->setMethods(['getEntityId', 'getOrigData', 'getData', 'getCategoryIds'])
            ->getMock();

        $this->page = $this->getMockBuilder(Page::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'load'])
            ->getMock();

        $this->plugin = $objectManager->getObject(
            Product::class,
            [
                'page' => $this->page
            ]
        );
    }

    public function testAfterGetIdentities()
    {
        $baseIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
        ];
        $id = 12345;
        $pageId = 1;
        $expectedIdentities = [
            'SomeCacheId',
            'AnotherCacheId',
            Page::CACHE_TAG . '_' . $pageId,
        ];

        $this->product->method('getEntityId')
            ->willReturn($id);
        $this->product->method('getOrigData')
            ->with('status')
            ->willReturn(2);
        $this->product->method('getData')
            ->with('status')
            ->willReturn(1);
        $this->page->method('getId')
            ->willReturn(1);
        $this->page->method('load')
            ->willReturnSelf();

        $identities = $this->plugin->afterGetIdentities($this->product, $baseIdentities);

        $this->assertEquals($expectedIdentities, $identities);
    }
}
