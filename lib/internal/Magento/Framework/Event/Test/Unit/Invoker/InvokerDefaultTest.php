<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Event\Test\Unit\Invoker;

use Magento\Framework\App\State;
use Magento\Framework\Event\Invoker\InvokerDefault;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Test for Magento\Framework\Event\Invoker\InvokerDefault.
 */
class InvokerDefaultTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_observerFactoryMock;

    /**
     * @var Observer|MockObject
     */
    protected $_observerMock;

    /**
     * @var MockObject
     */
    protected $_listenerMock;

    /**
     * @var MockObject
     */
    protected $_appStateMock;

    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var InvokerDefault
     */
    protected $_invokerDefault;

    /**
     * @var |Psr\Log|LoggerInterface
     */
    private $loggerMock;

    protected function setUp(): void
    {
        $this->_observerFactoryMock = $this->createMock(ObserverFactory::class);
        $this->_observerMock = $this->createMock(Observer::class);
        $this->_listenerMock = $this->createPartialMock(
            ObserverExample::class,
            ['execute']
        );
        $this->_appStateMock = $this->createMock(State::class);
        $this->_scopeConfigMock = $this->createMock(\Magento\Framework\App\Config::class);
        $this->loggerMock = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->_invokerDefault = new InvokerDefault(
            $this->_observerFactoryMock,
            $this->_appStateMock,
            $this->_scopeConfigMock,
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
        $this->expectException('LogicException');
        $notObserver = $this->getMockBuilder('NotObserver')
            ->getMock();
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
            State::MODE_DEVELOPER
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
        $notObserver = $this->getMockBuilder('NotObserver')
            ->getMock();
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
            State::MODE_PRODUCTION
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
    public static function dataProviderForMethodIsNotDefined()
    {
        return ['shared' => [true], 'non shared' => [false]];
    }

    public function testDispatchWithIfconfigFalse()
    {
        $this->_observerFactoryMock->expects($this->never())->method('get');
        $this->_observerFactoryMock->expects($this->never())->method('create');

        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            'test/path/value'
        )->willReturn(
            false
        );

        $this->_invokerDefault->dispatch(['ifconfig' => 'test/path/value'], $this->_observerMock);
    }

    public function testDispatchWithIfconfigTrue()
    {
        $this->_listenerMock->expects($this->once())->method('execute');
        $this->_scopeConfigMock->expects(
            $this->any()
        )->method(
            'isSetFlag'
        )->with(
            'test/path/value'
        )->willReturn(
            true
        );
        $this->_observerFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'class_name'
        )->willReturn(
            $this->_listenerMock
        );

        $this->_invokerDefault->dispatch(['instance' => 'class_name',
            'ifconfig' => 'test/path/value'], $this->_observerMock);
    }

    public function testDispatchWithIfconfigMissing()
    {
        $this->_listenerMock->expects($this->once())->method('execute');
        $this->_observerFactoryMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'class_name'
        )->willReturn(
            $this->_listenerMock
        );

        $this->_invokerDefault->dispatch(['instance' => 'class_name'], $this->_observerMock);
    }
}
