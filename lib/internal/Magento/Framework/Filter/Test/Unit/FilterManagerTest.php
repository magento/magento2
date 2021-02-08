<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Filter\Test\Unit;

class FilterManagerTest extends \PHPUnit\Framework\TestCase
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
        $factoryName = \Magento\Framework\Filter\Factory::class;
        $this->_factoryMock = $this->createPartialMock($factoryName, ['canCreateFilter', 'createFilter']);
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $this->equalTo($factoryName)
        )->willReturn(
            $this->_factoryMock
        );
        $this->_config =
            $this->createPartialMock(\Magento\Framework\Filter\FilterManager\Config::class, ['getFactories']);
        $this->_config->expects(
            $this->atLeastOnce()
        )->method(
            'getFactories'
        )->willReturn(
            [$factoryName]
        );
        $this->_filterManager = new \Magento\Framework\Filter\FilterManager($this->_objectManager, $this->_config);
    }

    public function testGetFilterFactories()
    {
        $this->initMocks();
        $method =
            new \ReflectionMethod(\Magento\Framework\Filter\FilterManager::class, 'getFilterFactories');
        $method->setAccessible(true);
        $this->assertEquals([$this->_factoryMock], $method->invoke($this->_filterManager));
    }

    /**
     */
    public function testGetFilterFactoriesWrongInstance()
    {
        $this->expectException(\UnexpectedValueException::class);
        $this->expectExceptionMessage('Filter factory must implement FilterFactoryInterface interface, stdClass was given.');

        $factoryName = \Magento\Framework\Filter\Factory::class;
        $this->_factoryMock = new \stdClass();
        $this->_objectManager = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $this->equalTo($factoryName)
        )->willReturn(
            $this->_factoryMock
        );
        $this->_config =
            $this->createPartialMock(\Magento\Framework\Filter\FilterManager\Config::class, ['getFactories']);
        $this->_config->expects(
            $this->atLeastOnce()
        )->method(
            'getFactories'
        )->willReturn(
            [$factoryName]
        );
        $this->_filterManager = new \Magento\Framework\Filter\FilterManager($this->_objectManager, $this->_config);

        $method = new \ReflectionMethod(\Magento\Framework\Filter\FilterManager::class, 'getFilterFactories');
        $method->setAccessible(true);
        $method->invoke($this->_filterManager);
    }

    public function testCreateFilterInstance()
    {
        $this->initMocks();
        $filterMock = $this->getMockBuilder('FactoryInterface')->getMock();
        $this->configureFactoryMock($filterMock, 'alias', ['123']);

        $method = new \ReflectionMethod(\Magento\Framework\Filter\FilterManager::class, 'createFilterInstance');
        $method->setAccessible(true);
        $this->assertEquals($filterMock, $method->invoke($this->_filterManager, 'alias', ['123']));
    }

    /**
     */
    public function testCreateFilterInstanceWrongAlias()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Filter was not found by given alias wrongAlias');

        $this->initMocks();
        $filterAlias = 'wrongAlias';
        $this->_factoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'canCreateFilter'
        )->with(
            $this->equalTo($filterAlias)
        )->willReturn(
            false
        );

        $method = new \ReflectionMethod(\Magento\Framework\Filter\FilterManager::class, 'createFilterInstance');
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
        )->willReturn(
            true
        );

        $this->_factoryMock->expects(
            $this->atLeastOnce()
        )->method(
            'createFilter'
        )->with(
            $this->equalTo($alias),
            $this->equalTo($arguments)
        )->willReturn(
            $filter
        );
    }

    public function testCall()
    {
        $value = 'testValue';
        $this->initMocks();
        $filterMock = $this->getMockBuilder('FactoryInterface')->setMethods(['filter'])->getMock();
        $filterMock->expects(
            $this->atLeastOnce()
        )->method(
            'filter'
        )->with(
            $this->equalTo($value)
        )->willReturn(
            $value
        );
        $this->configureFactoryMock($filterMock, 'alias', ['123']);
        $this->assertEquals($value, $this->_filterManager->alias($value, ['123']));
    }
}
