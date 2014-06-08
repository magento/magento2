<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Framework\App\Cache;

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
    const CACHE_TYPE = 'Magento\Framework\Cache\FrontendInterface';

    protected function setUp()
    {
        $this->_typesArray = [
            self::TYPE_KEY => [
                'label' => 'Type Label',
                'description' => 'Type Description'
            ]
        ];
        $this->_config = $this->getMock(
            'Magento\Framework\Cache\ConfigInterface',
            ['getTypes', 'getType'],
            [],
            '',
            false
        );
        $this->_config->expects($this->any())->method('getTypes')->will($this->returnValue($this->_typesArray));

        $cacheState = $this->getMock(
            'Magento\Framework\App\Cache\StateInterface',
            ['isEnabled', 'setEnabled', 'persist'],
            [],
            '',
            false
        );
        $cacheState->expects($this->any())->method('isEnabled')->will($this->returnValue(self::IS_CACHE_ENABLED));
        $cacheBlockMock = $this->getMock(self::CACHE_TYPE, [], [], '', false);
        $factory = $this->getMock('Magento\Framework\App\Cache\InstanceFactory', ['get'], [], '', false);
        $factory->expects($this->any())->method('get')->with(self::CACHE_TYPE)->will(
            $this->returnValue($cacheBlockMock)
        );
        $this->_cache = $this->getMock(
            'Magento\Framework\App\CacheInterface',
            ['load', 'getFrontend', 'save', 'remove', 'clean'],
            [],
            '',
            false
        );

        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->_typeList = $objectHelper->getObject(
            'Magento\Framework\App\Cache\TypeList',
            [
                'config' => $this->_config,
                'cacheState' => $cacheState,
                'factory' => $factory,
                'cache' => $this->_cache
            ]
        );
    }

    public function testGetTypes()
    {
        $expectation = [
            self::TYPE_KEY => $this->_getPreparedType()
        ];
        $this->assertEquals($expectation, $this->_typeList->getTypes());
    }

    public function testGetInvalidated()
    {
        $expectation = [self::TYPE_KEY => $this->_getPreparedType()];
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue(serialize($this->_typesArray))
        );
        $this->assertEquals($expectation, $this->_typeList->getInvalidated());
    }

    public function testInvalidate()
    {
        // there are no invalidated types
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue([])
        );
        $expectedInvalidated = [
            self::TYPE_KEY => 1
        ];
        $this->_cache->expects($this->once())->method('save')->with(
            serialize($expectedInvalidated),
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
            self::TYPE_KEY => 1
        ];
        $this->_cache->expects($this->once())->method('save')->with(
            serialize($expectedInvalidated),
            TypeList::INVALIDATED_TYPES
        );
        $this->_typeList->invalidate([self::TYPE_KEY]);
    }

    public function testCleanType()
    {
        $this->_cache->expects($this->once())->method('load')->with(TypeList::INVALIDATED_TYPES)->will(
            $this->returnValue(serialize($this->_typesArray))
        );
        $this->_config->expects($this->once())->method('getType')->with(self::TYPE_KEY)->will(
            $this->returnValue(['instance' => self::CACHE_TYPE])
        );
        unset($this->_typesArray[self::TYPE_KEY]);
        $this->_cache->expects($this->once())->method('save')->with(
            serialize($this->_typesArray),
            TypeList::INVALIDATED_TYPES
        );
        $this->_typeList->cleanType(self::TYPE_KEY);
    }

    /**
     * Returns prepared type
     *
     * @return \Magento\Framework\Object
     */
    private function _getPreparedType()
    {
        return new \Magento\Framework\Object(
            [
                'id' => self::TYPE_KEY,
                'cache_type' => $this->_typesArray[self::TYPE_KEY]['label'],
                'description' => $this->_typesArray[self::TYPE_KEY]['description'],
                'tags' => '',
                'status' => self::IS_CACHE_ENABLED
            ]
        );
    }
}
