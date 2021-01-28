<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Event\Test\Unit\Invoker;

/**
 * Test for Magento\Framework\Event\Invoker\InvokerDefault.
 */
class InvokerDefaultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_observerFactoryMock;

    /**
     * @var \Magento\Framework\Event\Observer|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_observerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_listenerMock;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $_appStateMock;

    /**
     * @var \Magento\Framework\Event\Invoker\InvokerDefault
     */
    protected $_invokerDefault;

    /**
     * @var |Psr\Log|LoggerInterface
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->_observerFactoryMock = $this->createMock(\Magento\Framework\Event\ObserverFactory::class);
        $this->_observerMock = $this->createMock(\Magento\Framework\Event\Observer::class);
        $this->_listenerMock = $this->createPartialMock(
            \Magento\Framework\Event\Test\Unit\Invoker\ObserverExample::class,
            ['execute']
        );
        $this->_appStateMock = $this->createMock(\Magento\Framework\App\State::class);
        $this->loggerMock = $this->createMock(\Psr\Log\LoggerInterface::class);

        $this->_invokerDefault = new \Magento\Framework\Event\Invoker\InvokerDefault(
            $this->_observerFactoryMock,
            $this->_appStateMock,
            $this->loggerMock
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
        )->willReturn(
            $this->_listenerMock
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
        )->willReturn(
            $this->_listenerMock
        );

        $this->_invokerDefault->dispatch(
            ['shared' => true, 'instance' => 'class_name', 'method' => 'method_name', 'name' => 'observer'],
            $this->_observerMock
        );
    }

    /**
     * @param string $shared
     * @dataProvider dataProviderForMethodIsNotDefined
     */
    public function testWrongInterfaceCallWithEnabledDeveloperMode($shared)
    {
        $this->expectException(\LogicException::class);

        $notObserver = $this->getMockBuilder('NotObserver')->getMock();
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            'class_name'
        )->willReturn(
            $notObserver
        );
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'class_name'
        )->willReturn(
            $notObserver
        );
        $this->_appStateMock->expects(
            $this->once()
        )->method(
            'getMode'
        )->willReturn(
            \Magento\Framework\App\State::MODE_DEVELOPER
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
        )->willReturn(
            $notObserver
        );
        $this->_observerFactoryMock->expects(
            $this->any()
        )->method(
            'get'
        )->with(
            'class_name'
        )->willReturn(
            $notObserver
        );
        $this->_appStateMock->expects(
            $this->exactly(1)
        )->method(
            'getMode'
        )->willReturn(
            \Magento\Framework\App\State::MODE_PRODUCTION
        );

        $this->loggerMock->expects($this->once())->method('warning');

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
