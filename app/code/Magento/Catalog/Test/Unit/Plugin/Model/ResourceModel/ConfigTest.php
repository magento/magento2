<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Plugin\Model\ResourceModel;

use Magento\Catalog\Model\ResourceModel\Config as ConfigResourceModel;
use Magento\Catalog\Plugin\Model\ResourceModel\Config;
use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject
     */
    private $cacheMock;

    /**
     * @var StateInterface|MockObject
     */
    private $cacheStateMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var ConfigResourceModel|MockObject
     */
    private $configResourceModelMock;

    protected function setUp(): void
    {
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->cacheStateMock = $this->getMockForAbstractClass(StateInterface::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->configResourceModelMock = $this->createMock(ConfigResourceModel::class);
    }

    public function testGetAttributesUsedInListingOnCacheDisabled()
    {
        $this->cacheMock->expects($this->never())->method('load');

        $this->assertEquals(
            ['attributes'],
            $this->getConfig(false)->aroundGetAttributesUsedInListing(
                $this->configResourceModelMock,
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
        $this->configResourceModelMock->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->configResourceModelMock->method('getStoreId')->willReturn($storeId);
        $cacheId = Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID
            . $entityTypeId
            . '_' . $storeId;
        $this->cacheMock->method('load')->with($cacheId)->willReturn($serializedAttributes);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedAttributes)
            ->willReturn($attributes);

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedInListing(
                $this->configResourceModelMock,
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
        $this->configResourceModelMock->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->configResourceModelMock->method('getStoreId')->willReturn($storeId);
        $cacheId = Config::PRODUCT_LISTING_ATTRIBUTES_CACHE_ID
            . $entityTypeId
            . '_' . $storeId;
        $this->cacheMock->method('load')->with($cacheId)->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($attributes)
            ->willReturn($serializedAttributes);
        $this->cacheMock->method('save')->with(
            $serializedAttributes,
            $cacheId,
            [
                Type::CACHE_TAG,
                Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedInListing(
                $this->configResourceModelMock,
                $this->mockPluginProceed($attributes)
            )
        );
    }

    public function testGetAttributesUsedForSortByOnCacheDisabled()
    {
        $this->cacheMock->expects($this->never())->method('load');

        $this->assertEquals(
            ['attributes'],
            $this->getConfig(false)->aroundGetAttributesUsedForSortBy(
                $this->configResourceModelMock,
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
        $this->configResourceModelMock->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->configResourceModelMock->method('getStoreId')->willReturn($storeId);
        $cacheId = Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cacheMock->method('load')->with($cacheId)->willReturn($serializedAttributes);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedAttributes)
            ->willReturn($attributes);

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedForSortBy(
                $this->configResourceModelMock,
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
        $this->configResourceModelMock->method('getEntityTypeId')->willReturn($entityTypeId);
        $this->configResourceModelMock->method('getStoreId')->willReturn($storeId);
        $cacheId = Config::PRODUCT_LISTING_SORT_BY_ATTRIBUTES_CACHE_ID
            . $entityTypeId . '_' . $storeId;
        $this->cacheMock->method('load')->with($cacheId)->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize');
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($attributes)
            ->willReturn($serializedAttributes);
        $this->cacheMock->method('save')->with(
            $serializedAttributes,
            $cacheId,
            [
                Type::CACHE_TAG,
                Attribute::CACHE_TAG
            ]
        );

        $this->assertEquals(
            $attributes,
            $this->getConfig(true)->aroundGetAttributesUsedForSortBy(
                $this->configResourceModelMock,
                $this->mockPluginProceed($attributes)
            )
        );
    }

    /**
     * @param bool $cacheEnabledFlag
     *
     * @return Config
     */
    protected function getConfig($cacheEnabledFlag)
    {
        $this->cacheStateMock->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn($cacheEnabledFlag);

        return (new ObjectManager($this))->getObject(
            Config::class,
            [
                'cache' => $this->cacheMock,
                'cacheState' => $this->cacheStateMock,
                'serializer' => $this->serializerMock,
            ]
        );
    }

    /**
     * @param mixed $returnValue
     *
     * @return callable
     */
    protected function mockPluginProceed($returnValue = null)
    {
        return static function () use ($returnValue) {
            return $returnValue;
        };
    }
}
