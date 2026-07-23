<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Console\Command;

use Magento\Config\Console\Command\EmulatedAdminhtmlAreaProcessor;
use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Config\ScopeInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EmulatedAdminhtmlAreaProcessorTest extends TestCase
{
    /**
     * The application scope manager.
     *
     * @var ScopeInterface|MockObject
     */
    private $scopeMock;

    /**
     * The application state manager.
     *
     * @var State|MockObject
     */
    private $stateMock;

    /**
     * Emulator adminhtml area for CLI command.
     *
     * @var EmulatedAdminhtmlAreaProcessor
     */
    private $emulatedAdminhtmlProcessorArea;

    protected function setUp(): void
    {
        $this->scopeMock = $this->createMock(ScopeInterface::class);
        $this->stateMock = $this->createPartialMock(State::class, ['emulateAreaCode']);

        $this->emulatedAdminhtmlProcessorArea = new EmulatedAdminhtmlAreaProcessor(
            $this->scopeMock,
            $this->stateMock
        );
    }

    public function testProcess()
    {
        $currentScope = 'currentScope';
        $callback = function () {
        };
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($currentScope);

        $this->scopeMock->expects($this->once())
            ->method('setCurrentScope')
            ->with($currentScope);

        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->with(Area::AREA_ADMINHTML, $this->isInstanceOf(\Closure::class))
            ->willReturn('result');

        $this->assertEquals('result', $this->emulatedAdminhtmlProcessorArea->process($callback));
    }

    public function testProcessWithException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage('Some Message');
        $currentScope = 'currentScope';
        $this->scopeMock->expects($this->once())
            ->method('getCurrentScope')
            ->willReturn($currentScope);

        $this->scopeMock->expects($this->once())
            ->method('setCurrentScope')
            ->with($currentScope);

        $this->stateMock->expects($this->once())
            ->method('emulateAreaCode')
            ->willThrowException(new \Exception('Some Message'));

        $this->emulatedAdminhtmlProcessorArea->process(function () {
        });
    }
}
