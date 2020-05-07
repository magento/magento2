<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\AbstractFactory;
use Magento\Framework\Filter\ArrayFilter;
use Magento\Framework\Filter\Sprintf;
use Magento\Framework\Filter\Template;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;

class AbstractFactoryTest extends TestCase
{
    /**
     * @var AbstractFactory
     */
    protected $_factory;

    /**
     * @var array
     */
    protected $_invokableList = [
        'sprintf' => Sprintf::class,
        'template' => Template::class,
        'arrayFilter' => ArrayFilter::class,
    ];

    /**
     * @var array
     */
    protected $_sharedList = [
        Template::class => true,
        ArrayFilter::class => false,
    ];

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    protected function setUp(): void
    {
        $this->_objectManager = $this->getMockForAbstractClass(ObjectManagerInterface::class);

        $this->_factory = $this->getMockForAbstractClass(
            AbstractFactory::class,
            ['objectManger' => $this->_objectManager]
        );
        $property = new \ReflectionProperty(AbstractFactory::class, 'invokableClasses');
        $property->setAccessible(true);
        $property->setValue($this->_factory, $this->_invokableList);

        $property = new \ReflectionProperty(AbstractFactory::class, 'shared');
        $property->setAccessible(true);
        $property->setValue($this->_factory, $this->_sharedList);
    }

    /**
     * @dataProvider canCreateFilterDataProvider
     * @param string $alias
     * @param bool $expectedResult
     */
    public function testCanCreateFilter($alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_factory->canCreateFilter($alias));
    }

    /**
     * @return array
     */
    public function canCreateFilterDataProvider()
    {
        return [['arrayFilter', true], ['notExist', false]];
    }

    /**
     * @dataProvider isSharedDataProvider
     * @param string $alias
     * @param bool $expectedResult
     */
    public function testIsShared($alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_factory->isShared($alias));
    }

    /**
     * @return array
     */
    public function isSharedDataProvider()
    {
        return [
            'shared' => [Template::class, true],
            'not shared' => [ArrayFilter::class, false],
            'default value' => [Sprintf::class, true]
        ];
    }

    /**
     * @dataProvider createFilterDataProvider
     * @param string $alias
     * @param array $arguments
     * @param bool $isShared
     */
    public function testCreateFilter($alias, $arguments, $isShared)
    {
        $property = new \ReflectionProperty(AbstractFactory::class, 'sharedInstances');
        $property->setAccessible(true);

        $filterMock = $this->getMockBuilder('FactoryInterface')
            ->setMethods(['filter'])->getMock();
        $this->_objectManager->expects(
            $this->atLeastOnce()
        )->method(
            'create'
        )->with(
            $this->equalTo($this->_invokableList[$alias]),
            $this->equalTo($arguments)
        )->willReturn(
            $filterMock
        );

        $this->assertEquals($filterMock, $this->_factory->createFilter($alias, $arguments));
        if ($isShared) {
            $sharedList = $property->getValue($this->_factory);
            $this->assertArrayHasKey($alias, $sharedList);
            $this->assertEquals($filterMock, $sharedList[$alias]);
        } else {
            $this->assertEmpty($property->getValue($this->_factory));
        }
    }

    /**
     * @return array
     */
    public function createFilterDataProvider()
    {
        return [
            'not shared with args' => ['arrayFilter', ['123', '231'], false],
            'not shared without args' => ['arrayFilter', [], true],
            'shared' => ['template', [], true],
            'default shared' => ['sprintf', [], true]
        ];
    }
}
