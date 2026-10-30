<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Test\Unit;

use Magento\Framework\Filter\AbstractFactory;
use Magento\Framework\Filter\ArrayFilter;
use Magento\Framework\Filter\Sprintf;
use Magento\Framework\Filter\Template;
use Magento\Framework\ObjectManagerInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;

class AbstractFactoryTest extends TestCase
{
    use MockCreationTrait;

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
        $this->_objectManager = $this->createMock(ObjectManagerInterface::class);

        // Use getMockBuilder with onlyMethods([]) to allow real implementations
        // This creates a partial mock that doesn't override any methods
        $this->_factory = $this->getMockBuilder(AbstractFactory::class)
            ->setConstructorArgs([$this->_objectManager])
            ->onlyMethods([])
            ->getMock();
            
        $property = new \ReflectionProperty(AbstractFactory::class, 'invokableClasses');
        $property->setValue($this->_factory, $this->_invokableList);

        $property = new \ReflectionProperty(AbstractFactory::class, 'shared');
        $property->setValue($this->_factory, $this->_sharedList);
    }

    /**     * @param string $alias
     * @param bool $expectedResult
     */
    #[DataProvider('canCreateFilterDataProvider')]
    public function testCanCreateFilter($alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_factory->canCreateFilter($alias));
    }

    /**
     * @return array
     */
    public static function canCreateFilterDataProvider()
    {
        return [['arrayFilter', true], ['notExist', false]];
    }

    /**     * @param string $alias
     * @param bool $expectedResult
     */
    #[DataProvider('isSharedDataProvider')]
    public function testIsShared($alias, $expectedResult)
    {
        $this->assertEquals($expectedResult, $this->_factory->isShared($alias));
    }

    /**
     * @return array
     */
    public static function isSharedDataProvider()
    {
        return [
            'shared' => [Template::class, true],
            'not shared' => [ArrayFilter::class, false],
            'default value' => [Sprintf::class, true]
        ];
    }

    /**
     * @param string $alias
     * @param array $arguments
     * @param bool $isShared
     */
    #[DataProvider('createFilterDataProvider')]
    public function testCreateFilter($alias, $arguments, $isShared)
    {
        $property = new \ReflectionProperty(AbstractFactory::class, 'sharedInstances');

        $filterMock = $this->createPartialMockWithReflection(
            \stdClass::class,
            ['filter']
        );
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
    public static function createFilterDataProvider()
    {
        return [
            'not shared with args' => ['arrayFilter', ['123', '231'], false],
            'not shared without args' => ['arrayFilter', [], true],
            'shared' => ['template', [], true],
            'default shared' => ['sprintf', [], true]
        ];
    }
}
