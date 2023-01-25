<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Plugin\Model\ResourceModel\Entity;

use Magento\Eav\Model\Cache\Type;
use Magento\Eav\Model\Entity\Attribute;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Eav\Plugin\Model\ResourceModel\Entity\Attribute as AttributeResourcePlugin;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AttributeTest extends TestCase
{
    /**
     * @var CacheInterface|MockObject
     */
    protected $cacheMock;

    /**
     * @var StateInterface|MockObject
     */
    protected $cacheStateMock;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    /**
     * @var AttributeResource|MockObject
     */
    private $attributeResourceMock;

    /**
     * @var AttributeResourcePlugin
     */
    private $attributeResourcePlugin;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->cacheMock = $this->getMockForAbstractClass(CacheInterface::class);
        $this->cacheStateMock = $this->getMockForAbstractClass(StateInterface::class);
        $this->attributeResourceMock = $this->createMock(AttributeResource::class);
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);
        $this->attributeResourcePlugin = $objectManager->getObject(
            AttributeResourcePlugin::class,
            [
                'cache' => $this->cacheMock,
                'cacheState' => $this->cacheStateMock,
                'serializer' => $this->serializerMock
            ]
        );
    }

    public function testAroundGetStoreLabelsByAttributeIdCacheIsDisabled()
    {
        $attributeId = 1;
        $this->cacheMock->expects($this->never())
            ->method('load');
        $this->cacheStateMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(false);

        $isProceedCalled = false;
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function ($attributeId) use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->attributeResourcePlugin->aroundGetStoreLabelsByAttributeId(
            $this->attributeResourceMock,
            $proceed,
            $attributeId
        );
        $this->assertTrue($isProceedCalled);
    }

    public function testAroundGetStoreLabelsByAttributeIdCacheExists()
    {
        $attributeId = 1;
        $attributes = ['foo' => 'bar'];
        $serializedAttributes = 'serialized attributes';
        $cacheId = AttributeResourcePlugin::STORE_LABEL_ATTRIBUTE . $attributeId;
        $this->cacheStateMock->expects($this->once())
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheId)
            ->willReturn($serializedAttributes);
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with($serializedAttributes)
            ->willReturn($attributes);

        $isProceedCalled = false;
        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function ($attributeId) use (&$isProceedCalled) {
            $isProceedCalled = true;
        };

        $this->assertEquals(
            $attributes,
            $this->attributeResourcePlugin->aroundGetStoreLabelsByAttributeId(
                $this->attributeResourceMock,
                $proceed,
                $attributeId
            )
        );
        $this->assertFalse($isProceedCalled);
    }

    public function testAroundGetStoreLabelsByAttributeIdCacheDoesNotExist()
    {
        $attributeId = 1;
        $attributes = ['foo' => 'bar'];
        $serializedAttributes = 'serialized attributes';
        $cacheId = AttributeResourcePlugin::STORE_LABEL_ATTRIBUTE . $attributeId;
        $this->cacheStateMock->expects($this->exactly(2))
            ->method('isEnabled')
            ->with(Type::TYPE_IDENTIFIER)
            ->willReturn(true);
        $this->cacheMock->expects($this->once())
            ->method('load')
            ->with($cacheId)
            ->willReturn(false);
        $this->serializerMock->expects($this->never())
            ->method('unserialize')
            ->with($serializedAttributes)
            ->willReturn($attributes);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($attributes)
            ->willReturn($serializedAttributes);
        $this->cacheMock->expects($this->once())
            ->method('save')
            ->with(
                $serializedAttributes,
                $cacheId,
                [
                    Type::CACHE_TAG,
                    Attribute::CACHE_TAG
                ]
            );

        // @SuppressWarnings(PHPMD.UnusedFormalParameter)
        $proceed = function ($attributeId) use ($attributes) {
            return $attributes;
        };

        $this->assertEquals(
            $attributes,
            $this->attributeResourcePlugin->aroundGetStoreLabelsByAttributeId(
                $this->attributeResourceMock,
                $proceed,
                $attributeId
            )
        );
    }
}
