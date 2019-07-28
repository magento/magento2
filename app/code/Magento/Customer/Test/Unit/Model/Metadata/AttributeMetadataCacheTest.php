<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Model\Metadata;

use Magento\Config\App\Config\Type\System;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\Metadata\AttributeMetadataCache;
use Magento\Customer\Model\Metadata\AttributeMetadataHydrator;
use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * AttributeMetadataCache Test
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AttributeMetadataCacheTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $cacheMock;

    /**
     * @var StateInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $stateMock;

    /**
     * @var AttributeMetadataHydrator|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadataHydratorMock;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    /**
     * @var AttributeMetadataCache|\PHPUnit_Framework_MockObject_MockObject
     */
    private $attributeMetadataCache;

    /**
     * @var StoreInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeMock;

    /**
     * @var StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $storeManagerMock;
    
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->cacheMock = $this->createMock(CacheInterface::class);
        $this->stateMock = $this->createMock(StateInterface::class);
        $this->serializerMock = $this->createMock(SerializerInterface::class);
        $this->attributeMetadataHydratorMock = $this->createMock(AttributeMetadataHydrator::class);
        $this->storeMock = $this->createMock(StoreInterface::class);
        $this->storeManagerMock = $this->createMock(StoreManagerInterface::class);
        $this->storeManagerMock->method('getStore')->willReturn($this->storeMock);
        $this->storeMock->method('getId')->willReturn(1);
        $this->attributeMetadataCache = $objectManager->getObject(
            AttributeMetadataCache::class,
            [
                'cache' => $this->cacheMock,
                'state' => $this->stateMock,
                'serializer' => $this->serializerMock,
                'attributeMetadataHydrator' => $this->attributeMetadataHydratorMock,
                'storeManager' => $this->storeManagerMock
            ]
        );
    }

    public function testLoadCacheDisabled()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->cacheMock->expects($this->never())
            ->method('load');
        $this->assertFalse($this->attributeMetadataCache->load($entityType, $suffix));
        // Make sure isEnabled called once
        $this->attributeMetadataCache->load($entityType, $suffix);
    }

    public function testLoadNoCache()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $storeId = 1;
        $cacheKey = AttributeMetadataCache::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix . $storeId;
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn(false);
        $this->assertFalse($this->attributeMetadataCache->load($entityType, $suffix));
    }

    public function testLoad()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $storeId = 1;
        $cacheKey = AttributeMetadataCache::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix . $storeId;
        $serializedString = 'serialized string';
        $attributeMetadataOneData = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
        ];
        $attributesMetadataData = [$attributeMetadataOneData];
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheKey)
            ->willReturn($serializedString);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedString)
            ->willReturn($attributesMetadataData);
        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $attributeMetadataMock */
        $attributeMetadataMock = $this->createMock(AttributeMetadataInterface::class);
        $this->attributeMetadataHydratorMock->expects($this->at(0))
            ->method('hydrate')
            ->with($attributeMetadataOneData)
            ->willReturn($attributeMetadataMock);
        $attributesMetadata = $this->attributeMetadataCache->load($entityType, $suffix);
        $this->assertInternalType(
            \PHPUnit\Framework\Constraint\IsType::TYPE_ARRAY,
            $attributesMetadata
        );
        $this->assertArrayHasKey(
            0,
            $attributesMetadata
        );
        $this->assertInstanceOf(
            AttributeMetadataInterface::class,
            $attributesMetadata[0]
        );
    }

    public function testSaveCacheDisabled()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $attributes = [['foo'], ['bar']];
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->attributeMetadataCache->save($entityType, $attributes, $suffix);
        $this->assertEquals(
            $attributes,
            $this->attributeMetadataCache->load($entityType, $suffix)
        );
    }

    public function testSave()
    {
        $entityType = 'EntityType';
        $suffix = 'none';
        $storeId = 1;
        $cacheKey = AttributeMetadataCache::ATTRIBUTE_METADATA_CACHE_PREFIX . $entityType . $suffix . $storeId;
        $serializedString = 'serialized string';
        $attributeMetadataOneData = [
            'attribute_code' => 'attribute_code',
            'frontend_input' => 'hidden',
        ];
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);

        /** @var AttributeMetadataInterface|\PHPUnit_Framework_MockObject_MockObject $attributeMetadataMock */
        $attributeMetadataMock = $this->createMock(AttributeMetadataInterface::class);
        $attributesMetadata = [$attributeMetadataMock];
        $this->attributeMetadataHydratorMock->expects($this->once())
            ->method('extract')
            ->with($attributeMetadataMock)
            ->willReturn($attributeMetadataOneData);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with([$attributeMetadataOneData])
            ->willReturn($serializedString);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedString,
                $cacheKey,
                [
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG,
                    System::CACHE_TAG
                ]
            );
        $this->attributeMetadataCache->save($entityType, $attributesMetadata, $suffix);
        $this->assertSame(
            $attributesMetadata,
            $this->attributeMetadataCache->load($entityType, $suffix)
        );
    }

    public function testCleanCacheDisabled()
    {
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);
        $this->cacheMock->expects($this->never())
            ->method('clean');
        $this->attributeMetadataCache->clean();
    }

    public function testClean()
    {
        $this->stateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('clean');
        $this->attributeMetadataCache->clean();
    }
}
