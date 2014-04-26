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

/**
 * \Magento\Framework\Cache\Backend\Decorator\AbstractDecorator test case
 */
namespace Magento\Framework\Cache\Backend\Decorator;

class DecoratorAbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Zend_Cache_Backend_File
     */
    protected $_mockBackend;

    protected function setUp()
    {
        $this->_mockBackend = $this->getMock('Zend_Cache_Backend_File');
    }

    protected function tearDown()
    {
        unset($this->_mockBackend);
    }

    public function testConstructor()
    {
        $options = array('concrete_backend' => $this->_mockBackend, 'testOption' => 'testOption');

        $decorator = $this->getMockForAbstractClass(
            'Magento\Framework\Cache\Backend\Decorator\AbstractDecorator',
            array($options)
        );

        $backendProperty = new \ReflectionProperty(
            'Magento\Framework\Cache\Backend\Decorator\AbstractDecorator',
            '_backend'
        );
        $backendProperty->setAccessible(true);

        $optionsProperty = new \ReflectionProperty(
            'Magento\Framework\Cache\Backend\Decorator\AbstractDecorator',
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
        $this->getMockForAbstractClass('Magento\Framework\Cache\Backend\Decorator\AbstractDecorator', array($options));
    }

    public function constructorExceptionDataProvider()
    {
        return array(
            'empty' => array(array()),
            'wrong_class' => array(array('concrete_backend' => $this->getMock('Test_Class')))
        );
    }

    /**
     * @dataProvider allMethodsDataProvider
     */
    public function testAllMethods($methodName)
    {
        $this->_mockBackend->expects($this->once())->method($methodName);

        $decorator = $this->getMockForAbstractClass(
            'Magento\Framework\Cache\Backend\Decorator\AbstractDecorator',
            array(array('concrete_backend' => $this->_mockBackend))
        );

        call_user_func(array($decorator, $methodName), null, null);
    }

    public function allMethodsDataProvider()
    {
        $return = array();
        $allMethods = array(
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
            'isAutomaticCleaningAvailable',
            'getTmpDir'
        );
        foreach ($allMethods as $method) {
            $return[$method] = array($method);
        }
        return $return;
    }
}
