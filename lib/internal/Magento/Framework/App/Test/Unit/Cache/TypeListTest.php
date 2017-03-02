<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Cache;

use \Magento\Framework\App\Cache\TypeList;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Test class for \Magento\Framework\App\Cache\TypeList
 */
class TypeListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Cache\TypeList
     */
    protected $_typeList;

    /**
     * @var \Magento\Framework\App\CacheInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cache;

    /**
     * @var array
     */
    protected $_typesArray;

    /**
     * @var \Magento\Framework\Cache\ConfigInterface|\PHPUnit_Framework_MockObject_MockObject
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
    const CACHE_TYPE = \Magento\Framework\Cache\FrontendInterface::class;

    /**
     * @var SerializerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $serializerMock;

    protected function setUp()
    {
        $this->_typesArray = [
            self::TYPE_KEY => [
                'label' => 'Type Label',
                'description' => 'Type Description',
            ],
        ];
        $this->_config = $this->getMock(
            \Magento\Framework\Cache\ConfigInterface::class,
            ['getTypes', 'getType'],
            [],
            '',
            false
        );
        $this->_config->expects($this->any())->method('getTypes')->will($this->returnValue($this->_typesArray));

        $cacheState = $this->getMock(
            \Magento\Framework\App\Cache\StateInterface::class,
            ['isEnabled', 'setEnabled', 'persist'],
            [],
            '',
            false
        );
        $cacheState->expects($this->any())->method('isEnabled')->will($this->returnValue(self::IS_CACHE_ENABLED));
        $cacheBlockMock = $this->getMock(self::CACHE_TYPE, [], [], '', false);
        $factory = $this->getMock(\Magento\Framework\App\Cache\InstanceFactory::class, ['get'], [], '', false);
        $factory->expects($this->any())->method('get')->with(self::CACHE_TYPE)->will(
            $this->returnValue($cacheBlockMock)
        );
        $this->_cache = $this->getMock(
            \Magento\Framework\App\CacheInterface::class,
            ['load', 'getFrontend', 'save', 'remove', 'clean'],
            [],
            '',
            false
        );
        $this->serializerMock = $this->getMock(SerializerInterface::class);

        $objectHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->_typeList = $objectHelper->getObject(
            \Magento\Framework\App\Cache\TypeList::class,
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
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue('serializedData')
        );
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($this->_typesArray);
        $this->assertEquals($expectation, $this->_typeList->getInvalidated());
    }

    public function testInvalidate()
    {
        // there are no invalidated types
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue([])
        );
        $expectedInvalidated = [
            self::TYPE_KEY => 1,
        ];
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expectedInvalidated)
            ->willReturn('serializedData');
        $this->_cache->expects($this->once())->method('save')->with(
            'serializedData',
            TypeList::INVALIDATED_TYPES
        );
        $this->_typeList->invalidate(self::TYPE_KEY);
    }

    public function testInvalidateList()
    {
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue([])
        );
        $expectedInvalidated = [
            self::TYPE_KEY => 1,
        ];
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($expectedInvalidated)
            ->willReturn('serializedData');
        $this->_cache->expects($this->once())->method('save')->with(
            'serializedData',
            TypeList::INVALIDATED_TYPES
        );
        $this->_typeList->invalidate([self::TYPE_KEY]);
    }

    public function testCleanType()
    {
        $this->serializerMock->expects($this->once())
            ->method('unserialize')
            ->with('serializedData')
            ->willReturn($this->_typesArray);
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue('serializedData')
        );
        $this->_config->expects($this->once())->method('getType')->with(self::TYPE_KEY)->will(
            $this->returnValue(['instance' => self::CACHE_TYPE])
        );
        unset($this->_typesArray[self::TYPE_KEY]);
        $this->serializerMock->expects($this->once())
            ->method('serialize')
            ->with($this->_typesArray)
            ->willReturn('serializedData');
        $this->_cache->expects($this->once())->method('save')->with(
            'serializedData',
            TypeList::INVALIDATED_TYPES
        );
        $this->_typeList->cleanType(self::TYPE_KEY);
    }

    /**
     * Returns prepared type
     *
     * @return \Magento\Framework\DataObject
     */
    private function _getPreparedType()
    {
        return new \Magento\Framework\DataObject(
            [
                'id' => self::TYPE_KEY,
                'cache_type' => $this->_typesArray[self::TYPE_KEY]['label'],
                'description' => $this->_typesArray[self::TYPE_KEY]['description'],
                'tags' => '',
                'status' => self::IS_CACHE_ENABLED,
            ]
        );
    }
}
