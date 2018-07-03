<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator test case
 */
namespace Magento\Framework\Cache\Test\Unit\Backend\Decorator;

class DecoratorAbstractTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Zend_Cache_Backend_File
     */
    protected $_mockBackend;

    protected function setUp()
    {
        $this->_mockBackend = $this->createMock(\Zend_Cache_Backend_File::class);
    }

    protected function tearDown()
    {
        unset($this->_mockBackend);
    }

    public function testConstructor()
    {
        $options = ['concrete_backend' => $this->_mockBackend, 'testOption' => 'testOption'];

        $decorator = $this->getMockForAbstractClass(
            \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator::class,
            [$options]
        );

        $backendProperty = new \ReflectionProperty(
            \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator::class,
            '_backend'
        );
        $backendProperty->setAccessible(true);

        $optionsProperty = new \ReflectionProperty(
            \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator::class,
            '_decoratorOptions'
        );
        $optionsProperty->setAccessible(true);

        $this->assertSame($backendProperty->getValue($decorator), $this->_mockBackend);

        $this->assertArrayNotHasKey('concrete_backend', $optionsProperty->getValue($decorator));
        $this->assertArrayNotHasKey('testOption', $optionsProperty->getValue($decorator));
    }

    /**
     * @param array options
     * @expectedException \Zend_Cache_Exception
     * @dataProvider constructorExceptionDataProvider
     */
    public function testConstructorException($options)
    {
        $this->getMockForAbstractClass(\Magento\Framework\Cache\Backend\Decorator\AbstractDecorator::class, [$options]);
    }

    /**
     * @return array
     */
    public function constructorExceptionDataProvider()
    {
        return [
            'empty' => [[]],
            'wrong_class' => [['concrete_backend' => $this->getMockBuilder('Test_Class')->getMock()]]
        ];
    }

    /**
     * @dataProvider allMethodsDataProvider
     */
    public function testAllMethods($methodName)
    {
        $this->_mockBackend->expects($this->once())->method($methodName);

        $decorator = $this->getMockForAbstractClass(
            \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator::class,
            [['concrete_backend' => $this->_mockBackend]]
        );

        call_user_func([$decorator, $methodName], null, null);
    }

    /**
     * @return array
     */
    public function allMethodsDataProvider()
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
