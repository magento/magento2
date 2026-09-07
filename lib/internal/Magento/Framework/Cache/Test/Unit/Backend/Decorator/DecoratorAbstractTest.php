<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

/**
 * \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator test case
 *
 * @deprecated Tests deprecated class AbstractDecorator
 * @see \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator
 * @group legacy
 * @group disabled
 */
namespace Magento\Framework\Cache\Test\Unit\Backend\Decorator;

use Magento\Framework\Cache\Backend\Decorator\AbstractDecorator;
use PHPUnit\Framework\Attributes\DataProvider;

use PHPUnit\Framework\TestCase;

class DecoratorAbstractTest extends TestCase
{
    /**
     * @var \Zend_Cache_Backend_File
     */
    protected $_mockBackend;

    /**
     * Skip all tests as the class being tested is deprecated
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->markTestSkipped(
            'Test skipped: AbstractDecorator is deprecated. Use Symfony cache frontend decorators instead.'
        );
    }

    protected function tearDown(): void
    {
        unset($this->_mockBackend);
    }

    public function testConstructor()
    {
        $options = ['concrete_backend' => $this->_mockBackend, 'testOption' => 'testOption'];

        $decorator = $this->createMock(
            AbstractDecorator::class,
            [$options]
        );

        $backendProperty = new \ReflectionProperty(
            AbstractDecorator::class,
            '_backend'
        );

        $optionsProperty = new \ReflectionProperty(
            AbstractDecorator::class,
            '_decoratorOptions'
        );

        $this->assertSame($backendProperty->getValue($decorator), $this->_mockBackend);

        $this->assertArrayNotHasKey('concrete_backend', $optionsProperty->getValue($decorator));
        $this->assertArrayNotHasKey('testOption', $optionsProperty->getValue($decorator));
    }

    /**
     * @param array $options
     */
     #[DataProvider('constructorExceptionDataProvider')]
    public function testConstructorException($options)
    {
        if (!empty($options)) {
            $options['concrete_backend'] = $options['concrete_backend']($this);
        }

        $this->expectException('Zend_Cache_Exception');
        $this->getMockBuilder(AbstractDecorator::class)
            ->setConstructorArgs([$options])
            ->getMock();
    }

    /**
     * @return array
     */
    public static function constructorExceptionDataProvider()
    {
        return [
            'empty' => [[]],
            'wrong_class' => [[
                'concrete_backend' => static fn (self $testCase) => $testCase->getMockBuilder('Test_Class')
                    ->getMock()
            ]]
        ];
    }

    /**
     */
     #[DataProvider('allMethodsDataProvider')]
    public function testAllMethods($methodName)
    {
        $this->_mockBackend->expects($this->once())->method($methodName);

        $decorator = $this->createMock(
            AbstractDecorator::class,
            [['concrete_backend' => $this->_mockBackend]]
        );

        call_user_func([$decorator, $methodName], null, null);
    }

    /**
     * @return array
     */
    public static function allMethodsDataProvider()
    {
        $return = [];
        $allMethods = [
            'setDirectives',
            'load',
            'test',
            'save',
            'remove',
            'clean',
            'getIds',
            'getTags',
            'getIdsMatchingTags',
            'getIdsNotMatchingTags',
            'getIdsMatchingAnyTags',
            'getFillingPercentage',
            'getMetadatas',
            'touch',
            'getCapabilities',
            'setOption',
            'getLifetime',
            'getTmpDir',
        ];
        foreach ($allMethods as $method) {
            $return[$method] = [$method];
        }
        return $return;
    }
}
