<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Model\Indexer;

/**
 * Class CatalogRulePluginTest
 * @package Magento\PageCache\Test\Unit\Model\Indexer
 */
class CatalogRulePluginTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $cacheMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $poolMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $frontCache;

    /**
     * @var \Magento\PageCache\Model\Indexer\CatalogRulePlugin
     */
    protected $model;

    protected function setUp()
    {
        $this->configMock = $this->getMock(
            '\Magento\PageCache\Model\Config',
            ['isEnabled'],
            [],
            '',
            false
        );

        $this->cacheMock = $this->getMockForAbstractClass(
            '\Magento\Framework\App\CacheInterface',
            [],
            '',
            false,
            true,
            true,
            ['clean']
        );

        $this->frontCache = $this->getMockForAbstractClass(
            '\Magento\Framework\Cache\FrontendInterface',
            [],
            '',
            false,
            true,
            true,
            ['clean']
        );

        $this->poolMock = $this->getMock(
            '\Magento\Framework\App\Cache\Type\FrontendPool',
            ['get'],
            ['clean'],
            '',
            false
        );

        $this->model = new \Magento\PageCache\Model\Indexer\CatalogRulePlugin(
            $this->configMock,
            $this->cacheMock,
            $this->poolMock
        );
    }

    public function testAfterExecuteFull()
    {
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->willReturn(true);
        $this->poolMock->expects($this->once())
            ->method('get')
            ->with(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER)
            ->willReturn($this->frontCache);
        $this->frontCache->expects($this->once())->method('clean')
            ->with(
                \Zend_Cache::CLEANING_MODE_ALL,
                [
                    \Magento\Catalog\Model\Category::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG
                ]
            );
        $this->cacheMock->expects($this->once())
            ->method('clean')
            ->with(
                [
                    \Magento\Catalog\Model\Category::CACHE_TAG,
                    \Magento\Catalog\Model\Product::CACHE_TAG,
                    \Magento\Catalog\Model\Product\Compare\Item::CACHE_TAG,
                    \Magento\Wishlist\Model\Wishlist::CACHE_TAG
                ]
            );

        $this->model->afterExecuteFull(
            $this->getMockForAbstractClass(
                '\Magento\CatalogRule\Model\Indexer\AbstractIndexer',
                [],
                '',
                false
            )
        );
    }
}
