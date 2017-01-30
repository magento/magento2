<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

class FilterManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Filter\FilterManager
     */
    protected $_filterManager;

    /**
     * @var \Magento\Framework\Filter\Factory
     */
    protected $_factoryMock;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Filter\FilterManager\Config
     */
    protected $_config;

    protected function initMocks()
    {
        $factoryName = 'Magento\Framework\Filter\Factory';
        $this->_factoryMock = $this->getMock(
            $factoryName,
            ['canCreateFilter', 'createFilter'],
            [],
            '',
            false
        );
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $this->equalTo($factoryName)
        )->will(
            $this->returnValue($this->_factoryMock)
        );
        $this->_config = $this->getMock(
            '\Magento\Framework\Filter\FilterManager\Config',
            ['getFactories'],
            [],
            '',
            false
        );
        $this->_config->expects(
            $this->atLeastOnce()
        )->method(
            'getFactories'
        )->will(
            $this->returnValue([$factoryName])
        );
        $this->_filterManager = new \Magento\Framework\Filter\FilterManager($this->_objectManager, $this->_config);
    }

    public function testGetFilterFactories()
    {
        $this->initMocks();
        $method = new \ReflectionMethod('Magento\Framework\Filter\FilterManager', 'getFilterFactories');
        $method->setAccessible(true);
        $this->assertEquals([$this->_factoryMock], $method->invoke($this->_filterManager));
    }

    /**
     * @expectedException \UnexpectedValueException
     * @expectedExceptionMessage Filter factory must implement FilterFactoryInterface interface, stdClass was given.
     */
    public function testGetFilterFactoriesWrongInstance()
    {
        $factoryName = 'Magento\Framework\Filter\Factory';
        $this->_factoryMock = new \stdClass();
        $this->_objectManager = $this->getMock('Magento\Framework\ObjectManagerInterface');
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $this->equalTo($factoryName)
        )->will(
            $this->returnValue($this->_factoryMock)
        );
        $this->_config = $this->getMock(
            '\Magento\Framework\Filter\FilterManager\Config',
            ['getFactories'],
            [],
            '',
            false
        );
        $this->_config->expects(
            $this->atLeastOnce()
        )->method(
            'getFactories'
        )->will(
            $this->returnValue([$factoryName])
        );
        $this->_filterManager = new \Magento\Framework\Filter\FilterManager($this->_objectManager, $this->_config);

        $method = new \ReflectionMethod('Magento\Framework\Filter\FilterManager', 'getFilterFactories');
        $method->setAccessible(true);
        $method->invoke($this->_filterManager);
    }

    public function testCreateFilterInstance()
    {
        $this->initMocks();
        $filterMock = $this->getMock('FactoryInterface');
        $this->configureFactoryMock($filterMock, 'alias', ['123']);

        $method = new \ReflectionMethod('Magento\Framework\Filter\FilterManager', 'createFilterInstance');
        $method->setAccessible(true);
        $this->assertEquals($filterMock, $method->invoke($this->_filterManager, 'alias', ['123']));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Filter was not found by given alias wrongAlias
     */
    public function testCreateFilterInstanceWrongAlias()
    {
        $this->initMocks();
        $filterAlias = 'wrongAlias';
        $this->_factoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'canCreateFilter'
        )->with(
            $this->equalTo($filterAlias)
        )->will(
            $this->returnValue(false)
        );

        $method = new \ReflectionMethod('Magento\Framework\Filter\FilterManager', 'createFilterInstance');
        $method->setAccessible(true);
        $method->invoke($this->_filterManager, $filterAlias, []);
    }

    /**
     * @param object $filter
     * @param string $alias
     * @param array $arguments
     */
    protected function configureFactoryMock($filter, $alias, $arguments = [])
    {
        $this->_factoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'canCreateFilter'
        )->with(
            $this->equalTo($alias)
        )->will(
            $this->returnValue(true)
        );

        $this->_factoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'createFilter'
        )->with(
            $this->equalTo($alias),
            $this->equalTo($arguments)
        )->will(
            $this->returnValue($filter)
        );
    }

    public function testCall()
    {
        $value = 'testValue';
        $this->initMocks();
        $filterMock = $this->getMock('FactoryInterface', ['filter']);
        $filterMock->expects(
            $this->atLeastOnce()
        )->method(
            'filter'
        )->with(
            $this->equalTo($value)
        )->will(
            $this->returnValue($value)
        );
        $this->configureFactoryMock($filterMock, 'alias', ['123']);
        $this->assertEquals($value, $this->_filterManager->alias($value, ['123']));
    }
}
