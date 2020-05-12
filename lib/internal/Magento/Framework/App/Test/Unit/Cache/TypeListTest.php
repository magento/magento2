<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Cache;

use Magento\Framework\App\Cache\InstanceFactory;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\Cache\TypeList;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Cache\ConfigInterface;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;
use Magento\Framework\DataObject;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test class for \Magento\Framework\App\Cache\TypeList
 */
class TypeListTest extends TestCase
{
    /**
     * @var TypeList
     */
    protected $_typeList;

    /**
     * @var CacheInterface|MockObject
     */
    protected $_cache;

    /**
     * @var array
     */
    protected $_typesArray;

    /**
     * @var ConfigInterface|MockObject
     */
    protected $_config;

    /**
     * Type key for type list
     */
    const TYPE_KEY = 'type';

    /**
     * Expectation for type cache
     */
    const IS_CACHE_ENABLED = true;

    /**
     * Expected cache type
     */
    const CACHE_TYPE = TagScope::class;

    /**
     * @var SerializerInterface|MockObject
     */
    private $serializerMock;

    protected function setUp(): void
    {
        $this->_typesArray = [
            self::TYPE_KEY => [
                'name' => self::TYPE_KEY,
                'instance' => self::CACHE_TYPE,
                'label' => 'Type Label',
                'description' => 'Type Description',
            ],
        ];
        $this->_config = $this->createPartialMock(
            ConfigInterface::class,
            ['getTypes', 'getType']
        );
        $this->_config->expects($this->any())->method('getTypes')
            ->willReturn($this->_typesArray);
        $this->_config->expects($this->any())->method('getType')
            ->with(self::TYPE_KEY)
            ->willReturn($this->_typesArray[self::TYPE_KEY]);

        $cacheState = $this->createPartialMock(
            StateInterface::class,
            ['isEnabled', 'setEnabled', 'persist']
        );
        $cacheState->expects($this->any())->method('isEnabled')
            ->willReturn(self::IS_CACHE_ENABLED);
        $cacheTypeMock = $this->createMock(self::CACHE_TYPE);
        $cacheTypeMock->expects($this->any())->method('getTag')
            ->willReturn('TEST');
        $factory = $this->createPartialMock(InstanceFactory::class, ['get']);
        $factory->expects($this->any())->method('get')
            ->with(self::CACHE_TYPE)
            ->willReturn($cacheTypeMock);
        $this->_cache = $this->createPartialMock(
            CacheInterface::class,
            ['load', 'getFrontend', 'save', 'remove', 'clean']
        );
        $this->serializerMock = $this->getMockForAbstractClass(SerializerInterface::class);

        $objectHelper = new ObjectManager($this);
        $this->_typeList = $objectHelper->getObject(
            TypeList::class,
            [
                'config' => $this->_config,
                'cacheState' => $cacheState,
                'factory' => $factory,
                'cache' => $this->_cache,
                'serializer' => $this->serializerMock,
            ]
        );
    }

    public function testGetTypes()
    {
        $expectation = [
            self::TYPE_KEY => $this->_getPreparedType(),
        ];
        $this->assertEquals($expectation, $this->_typeList->getTypes());
    }

    public function testGetTypeLabels()
    {
        $expectation = [
            self::TYPE_KEY => $this->_typesArray[self::TYPE_KEY]['label'],
        ];
        $this->assertEquals($expectation, $this->_typeList->getTypeLabels());
    }

    public function testGetInvalidated()
    {
        $expectation = [self::TYPE_KEY => $this->_getPreparedType()];
        $this->_cache->expects($this->once())->method('load')
            ->with(TypeList::INVALIDATED_TYPES)
            ->willReturn('serializedData');
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($this->_typesArray);
        $this->assertEquals($expectation, $this->_typeList->getInvalidated());
    }

    public function testInvalidate()
    {
        // there are no invalidated types
        $this->_cache->expects($this->once())->method('load')
            ->with(TypeList::INVALIDATED_TYPES)
            ->willReturn([]);
        $expectedInvalidated = [
            self::TYPE_KEY => 1,
        ];
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expectedInvalidated)
            ->willReturn('serializedData');
        $this->_cache->expects($this->once())->method('save')
            ->with('serializedData', TypeList::INVALIDATED_TYPES);
        $this->_typeList->invalidate(self::TYPE_KEY);
    }

    public function testInvalidateList()
    {
        $this->_cache->expects($this->once())->method('load')
            ->with(TypeList::INVALIDATED_TYPES)
            ->willReturn([]);
        $expectedInvalidated = [
            self::TYPE_KEY => 1,
        ];
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expectedInvalidated)
            ->willReturn('serializedData');
        $this->_cache->expects($this->once())->method('save')
            ->with('serializedData', TypeList::INVALIDATED_TYPES);
        $this->_typeList->invalidate([self::TYPE_KEY]);
    }

    public function testCleanType()
    {
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($this->_typesArray);
        $this->_cache->expects($this->once())->method('load')
            ->with(TypeList::INVALIDATED_TYPES)
            ->willReturn('serializedData');
        $this->_config->expects($this->once())->method('getType')
            ->with(self::TYPE_KEY)
            ->willReturn(['instance' => self::CACHE_TYPE]);
        unset($this->_typesArray[self::TYPE_KEY]);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->_typesArray)
            ->willReturn('serializedData');
        $this->_cache->expects($this->once())->method('save')
            ->with('serializedData', TypeList::INVALIDATED_TYPES);
        $this->_typeList->cleanType(self::TYPE_KEY);
    }

    /**
     * Returns prepared type
     *
     * @return DataObject
     */
    private function _getPreparedType()
    {
        return new DataObject(
            [
                'id' => self::TYPE_KEY,
                'cache_type' => $this->_typesArray[self::TYPE_KEY]['label'],
                'description' => $this->_typesArray[self::TYPE_KEY]['description'],
                'tags' => 'TEST',
                'status' => (int)self::IS_CACHE_ENABLED,
            ]
        );
    }
}
