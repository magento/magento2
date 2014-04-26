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
namespace Magento\Framework\Event\Invoker;

class InvokerDefaultTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerFactoryMock;

    /**
     * @var \Magento\Framework\Event\Observer|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_observerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_listenerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_appStateMock;

    /**
     * @var \Magento\Framework\Event\Invoker\InvokerDefault
     */
    protected $_invokerDefault;

    protected function setUp()
    {
        $this->_observerFactoryMock = $this->getMock(
            'Magento\Framework\Event\ObserverFactory',
            array(),
            array(),
            '',
            false
        );
        $this->_observerMock = $this->getMock('Magento\Framework\Event\Observer', array(), array(), '', false);
        $this->_listenerMock = $this->getMock(
            'Magento_Some_Model_Observer_Some',
            array('method_name'),
            array(),
            '',
            false
        );
        $this->_appStateMock = $this->getMock('Magento\Framework\App\State', array(), array(), '', false);

        $this->_invokerDefault = new \Magento\Framework\Event\Invoker\InvokerDefault(
            $this->_observerFactoryMock,
            $this->_appStateMock
        );
    }

    public function testDispatchWithDisabledObserver()
    {
        $this->_observerFactoryMock->expects($this->never())->method('get');
        $this->_observerFactoryMock->expects($this->never())->method('create');

        $this->_invokerDefault->dispatch(array('disabled' => true), $this->_observerMock);
    }

    public function testDispatchWithNonSharedInstance()
    {
        $this->_listenerMock->expects($this->once())->method('method_name');
        $this->_observerFactoryMock->expects($this->never())->method('get');
        $this->_observerFactoryMock->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($this->_listenerMock)
        );

        $this->_invokerDefault->dispatch(
            array('shared' => false, 'instance' => 'class_name', 'method' => 'method_name', 'name' => 'observer'),
            $this->_observerMock
        );
    }

    public function testDispatchWithSharedInstance()
    {
        $this->_listenerMock->expects($this->once())->method('method_name');
        $this->_observerFactoryMock->expects($this->never())->method('create');
        $this->_observerFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($this->_listenerMock)
        );

        $this->_invokerDefault->dispatch(
            array('shared' => true, 'instance' => 'class_name', 'method' => 'method_name', 'name' => 'observer'),
            $this->_observerMock
        );
    }

    /**
     * @param string $shared
     * @dataProvider dataProviderForMethodIsNotDefined
     * @expectedException \LogicException
     */
    public function testMethodIsNotDefinedExceptionWithEnabledDeveloperMode($shared)
    {
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($this->_listenerMock)
        );
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($this->_listenerMock)
        );
        $this->_appStateMock->expects(
            $this->once()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER)
        );

        $this->_invokerDefault->dispatch(
            array(
                'shared' => $shared,
                'instance' => 'class_name',
                'method' => 'unknown_method_name',
                'name' => 'observer'
            ),
            $this->_observerMock
        );
    }

    /**
     * @param string $shared
     * @dataProvider dataProviderForMethodIsNotDefined
     */
    public function testMethodIsNotDefinedWithDisabledDeveloperMode($shared)
    {
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($this->_listenerMock)
        );
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($this->_listenerMock)
        );
        $this->_appStateMock->expects(
            $this->once()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\Framework\App\State::MODE_PRODUCTION)
        );

        $this->_invokerDefault->dispatch(
            array(
                'shared' => $shared,
                'instance' => 'class_name',
                'method' => 'unknown_method_name',
                'name' => 'observer'
            ),
            $this->_observerMock
        );
    }

    /**
     * @return array
     */
    public function dataProviderForMethodIsNotDefined()
    {
        return array('shared' => array(true), 'non shared' => array(false));
    }
}
