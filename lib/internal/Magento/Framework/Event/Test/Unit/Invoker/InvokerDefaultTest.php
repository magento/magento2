<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Test\Unit\Invoker;

class InvokerDefaultTest extends \PHPUnit\Framework\TestCase
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
        $this->_observerFactoryMock = $this->createMock(\Magento\Framework\Event\ObserverFactory::class);
        $this->_observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_listenerMock = $this->createPartialMock(
            \Magento\Framework\Event\Test\Unit\Invoker\ObserverExample::class,
            ['execute']
        );
        $this->_appStateMock = $this->createMock(\Magento\Framework\App\State::class);

        $this->_invokerDefault = new \Magento\Framework\Event\Invoker\InvokerDefault(
            $this->_observerFactoryMock,
            $this->_appStateMock
        );
    }

    public function testDispatchWithDisabledObserver()
    {
        $this->_observerFactoryMock->expects($this->never())->method('get');
        $this->_observerFactoryMock->expects($this->never())->method('create');

        $this->_invokerDefault->dispatch(['disabled' => true], $this->_observerMock);
    }

    public function testDispatchWithNonSharedInstance()
    {
        $this->_listenerMock->expects($this->once())->method('execute');
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
            ['shared' => false, 'instance' => 'class_name', 'name' => 'observer'],
            $this->_observerMock
        );
    }

    public function testDispatchWithSharedInstance()
    {
        $this->_listenerMock->expects($this->once())->method('execute');
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
            ['shared' => true, 'instance' => 'class_name', 'method' => 'method_name', 'name' => 'observer'],
            $this->_observerMock
        );
    }

    /**
     * @param string $shared
     * @dataProvider dataProviderForMethodIsNotDefined
     * @expectedException \LogicException
     */
    public function testWrongInterfaceCallWithEnabledDeveloperMode($shared)
    {
        $notObserver = $this->getMockBuilder('NotObserver')->getMock();
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($notObserver)
        );
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($notObserver)
        );
        $this->_appStateMock->expects(
            $this->once()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\Framework\App\State::MODE_DEVELOPER)
        );

        $this->_invokerDefault->dispatch(
            [
                'shared' => $shared,
                'instance' => 'class_name',
                'name' => 'observer',
            ],
            $this->_observerMock
        );
    }

    /**
     * @param string $shared
     * @dataProvider dataProviderForMethodIsNotDefined
     */
    public function testWrongInterfaceCallWithDisabledDeveloperMode($shared)
    {
        $notObserver = $this->getMockBuilder('NotObserver')->getMock();
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($notObserver)
        );
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'class_name'
        )->will(
            $this->returnValue($notObserver)
        );
        $this->_appStateMock->expects(
            $this->once()
        )->method(
            'getMode'
        )->will(
            $this->returnValue(\Magento\Framework\App\State::MODE_PRODUCTION)
        );

        $this->_invokerDefault->dispatch(
            [
                'shared' => $shared,
                'instance' => 'class_name',
                'method' => 'unknown_method_name',
                'name' => 'observer',
            ],
            $this->_observerMock
        );
    }

    /**
     * @return array
     */
    public function dataProviderForMethodIsNotDefined()
    {
        return ['shared' => [true], 'non shared' => [false]];
    }
}
