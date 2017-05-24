<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Plugin\Model\ResourceModel\Config;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $cache;

    /** @var \Magento\Framework\App\Cache\StateInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $cacheState;

    /** @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $serializer;

    /** @var \Magento\Catalog\Model\ResourceModel\Config|\PHPUnit_Framework_MockObject_MockObject */
    private $subject;

    protected function setUp()
    {
        $this->cache = $this->getMock(\Magento\Framework\App\CacheInterface::class);
        $this->cacheState = $this->getMock(\Magento\Framework\App\Cache\StateInterface::class);
        $this->serializer = $this->getMock(SerializerInterface::class);
        $this->subject = $this->getMock(\Magento\Catalog\Model\ResourceModel\Config::class, [], [], '', false);
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
        $serializedAttributes = '["attributes"]';
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID
            . $entityTypeId
            . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn($serializedAttributes);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedAttributes)
            ->willReturn($attributes);

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
        $serializedAttributes = '["attributes"]';
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID
            . $entityTypeId
            . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->serializer->expects($this->never())
            ->method('unserialize');
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($attributes)
            ->willReturn($serializedAttributes);
        $this->cache->expects($this->any())->method('save')->with(
            $serializedAttributes,
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
        $serializedAttributes = '["attributes"]';
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn($serializedAttributes);
        $this->serializer->expects($this->once())
            ->method('unserialize')
            ->with($serializedAttributes)
            ->willReturn($attributes);

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
        $serializedAttributes = '["attributes"]';
        $this->subject->expects($this->any())->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->subject->expects($this->any())->method('getStoreId')->willReturn($storeId);
        $cacheId = \Magento\Catalog\Plugin\Model\ResourceModel\Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cache->expects($this->any())->method('load')->with($cacheId)->willReturn(false);
        $this->serializer->expects($this->never())
            ->method('unserialize');
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($attributes)
            ->willReturn($serializedAttributes);
        $this->cache->expects($this->any())->method('save')->with(
            $serializedAttributes,
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
            \Magento\Catalog\Plugin\Model\ResourceModel\Config::class,
            [
                'cache' => $this->cache,
                'cacheState' => $this->cacheState,
                'serializer' => $this->serializer,
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
