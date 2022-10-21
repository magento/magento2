<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\Factory;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Filter\FilterManager\Config;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class FilterManagerTest extends TestCase
{
    /**
     * @var FilterManager
     */
    protected $_filterManager;

    /**
     * @var Factory
     */
    protected $_factoryMock;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Config
     */
    protected $_config;

    protected function initMocks()
    {
        $factoryName = Factory::class;
        $this->_factoryMock = $this->getMockBuilder($factoryName)
            ->disableOriginalConstructor()
            ->onlyMethods(['canCreateFilter', 'createFilter'])
            ->getMock();
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $factoryName
        )->willReturn(
            $this->_factoryMock
        );
        $this->_config =
            $this->createPartialMock(Config::class, ['getFactories']);
        $this->_config->expects(
            $this->atLeastOnce()
        )->method(
            'getFactories'
        )->willReturn(
            [$factoryName]
        );
        $this->_filterManager = new FilterManager($this->_objectManager, $this->_config);
    }

    public function testGetFilterFactories()
    {
        $this->initMocks();
        $method =
            new \ReflectionMethod(FilterManager::class, 'getFilterFactories');
        $method->setAccessible(true);
        $this->assertEquals([$this->_factoryMock], $method->invoke($this->_filterManager));
    }

    public function testGetFilterFactoriesWrongInstance()
    {
        $this->expectException('UnexpectedValueException');
        $this->expectExceptionMessage(sprintf(
            'Filter factory must implement %s interface, stdClass was given.',
            \Magento\Framework\Filter\FactoryInterface::class
        ));
        $factoryName = Factory::class;
        $this->_factoryMock = new \stdClass();
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $factoryName
        )->willReturn(
            $this->_factoryMock
        );
        $this->_config =
            $this->createPartialMock(Config::class, ['getFactories']);
        $this->_config->expects(
            $this->atLeastOnce()
        )->method(
            'getFactories'
        )->willReturn(
            [$factoryName]
        );
        $this->_filterManager = new FilterManager($this->_objectManager, $this->_config);

        $method = new \ReflectionMethod(FilterManager::class, 'getFilterFactories');
        $method->setAccessible(true);
        $method->invoke($this->_filterManager);
    }

    public function testCreateFilterInstance()
    {
        $this->initMocks();
        $filterMock = $this->getMockBuilder('FactoryInterface')
            ->getMock();
        $this->configureFactoryMock($filterMock, 'alias', ['123']);

        $method = new \ReflectionMethod(FilterManager::class, 'createFilterInstance');
        $method->setAccessible(true);
        $this->assertEquals($filterMock, $method->invoke($this->_filterManager, 'alias', ['123']));
    }

    public function testCreateFilterInstanceWrongAlias()
    {
        $this->expectException('InvalidArgumentException');
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

        $method = new \ReflectionMethod(FilterManager::class, 'createFilterInstance');
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
        $filterMock = $this->getMockBuilder('FactoryInterface')
            ->setMethods(['filter'])->getMock();
        $filterMock->expects(
            $this->atLeastOnce()
        )->method(
            'filter'
        )->with(
            $value
        )->willReturn(
            $value
        );
        $this->configureFactoryMock($filterMock, 'alias', ['123']);
        $this->assertEquals($value, $this->_filterManager->alias($value, ['123']));
    }
}
