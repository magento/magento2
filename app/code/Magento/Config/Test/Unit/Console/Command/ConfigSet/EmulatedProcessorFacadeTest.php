<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Console\Command\ConfigSet;

use Magento\Config\Console\Command\ConfigSet\EmulatedProcessorFacade;
use Magento\Config\Console\Command\ConfigSet\ProcessorFacadeFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use PHPUnit_Framework_MockObject_MockObject as Mock;

class EmulatedProcessorFacadeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EmulatedProcessorFacade
     */
    private $model;

    /**
     * @var ScopeInterface|Mock
     */
    private $scopeMock;

    /**
     * @var State|Mock
     */
    private $stateMock;

    /**
     * @var ProcessorFacadeFactory|Mock
     */
    private $processorFacadeFactory;

    protected function setUp()
    {
        $this->scopeMock = $this->getMockBuilder(ScopeInterface::class)
            ->getMockForAbstractClass();
        $this->stateMock = $this->getMockBuilder(State::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->processorFacadeFactory = $this->getMockBuilder(ProcessorFacadeFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new EmulatedProcessorFacade(
            $this->scopeMock,
            $this->stateMock,
            $this->processorFacadeFactory
        );
    }

    public function testProcess()
    {
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn('currentScope');
        $this->scopeMock->expects($this->once())
            ->method('setCurrentScope')
            ->with('currentScope');
        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode');

        $this->model->process(
            'test/test/test',
            'value',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            null,
            false
        );
    }
}
