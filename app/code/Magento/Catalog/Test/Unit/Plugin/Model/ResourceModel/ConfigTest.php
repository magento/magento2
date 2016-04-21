<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Plugin\Model\ResourceModel\Config */
    protected $config;

    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cache;

    /** @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $cacheState;

    /** @var \Magento\Catalog\Model\ResourceModel\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $subject;

    protected function setUp()
    {
        $this->cache = $this->getMock('Magento\Framework\App\CacheInterface');
        $this->cacheState = $this->getMock('Magento\Framework\App\Cache\StateInterface');
        $this->subject = $this->getMock('Magento\Catalog\Model\ResourceModel\Config', [], [], '', false);
    }

    public function testGetAttributesUsedInListingOnCacheDisabled()
    {
        $this->cache->expects($this->never())->method('load');

        $this->assertEquals(
            ['attributes'],
            $this->getConfig(false)->aroundGetAttributesUsedInListing(
                $this->subject,
                $this->mockPluginProceed(['attributes'])
            )
        );
    }

    public function testGetAttributesUsedInListingFromCache()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID
            . $entityTypeId
            . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(serialize($attributes));

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedInListing(
                $this->subject,
                $this->mockPluginProceed()
            )
        );
    }

    public function testGetAttributesUsedInListingWithCacheSave()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID
            . $entityTypeId
            . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->cache->expects($this->any())->method('save')->with(
            serialize($attributes),
            $cacheId,
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedInListing(
                $this->subject,
                $this->mockPluginProceed($attributes)
            )
        );
    }

    public function testGetAttributesUsedForSortByOnCacheDisabled()
    {
        $this->cache->expects($this->never())->method('load');

        $this->assertEquals(
            ['attributes'],
            $this->getConfig(false)->aroundGetAttributesUsedForSortBy(
                $this->subject,
                $this->mockPluginProceed(['attributes'])
            )
        );
    }

    public function testGetAttributesUsedForSortByFromCache()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(serialize($attributes));

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedForSortBy(
                $this->subject,
                $this->mockPluginProceed()
            )
        );
    }

    public function testGetAttributesUsedForSortByWithCacheSave()
    {
        $entityTypeId = 'type';
        $storeId = 'store';
        $attributes = ['attributes'];
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->cache->expects($this->any())->method('save')->with(
            serialize($attributes),
            $cacheId,
            [
                \Magento\Eav\Model\Cache\Type::CACHE_TAG,
                \Magento\Eav\Model\Entity\Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedForSortBy(
                $this->subject,
                $this->mockPluginProceed($attributes)
            )
        );
    }

    /**
     * @param bool $cacheEnabledFlag
     * @return \Magento\Catalog\Plugin\Model\ResourceModel\Config
     */
    protected function getConfig($cacheEnabledFlag)
    {
        $this->cacheState->expects($this->any())->method('isEnabled')
            ->with(\Magento\Eav\Model\Cache\Type::TYPE_IDENTIFIER)->willReturn($cacheEnabledFlag);
        return (new ObjectManager($this))->getObject(
            'Magento\Catalog\Plugin\Model\ResourceModel\Config',
            [
                'cache' => $this->cache,
                'cacheState' => $this->cacheState
            ]
        );
    }

    /**
     * @param mixed $returnValue
     * @return callable
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return function () use ($returnValue) {
            return $returnValue;
        };
    }
}
