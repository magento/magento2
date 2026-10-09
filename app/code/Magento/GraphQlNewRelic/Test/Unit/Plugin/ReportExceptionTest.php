<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GraphQlNewRelic\Test\Unit\Plugin;

use Magento\Framework\GraphQl\Exception\ExceptionFormatter;
use Magento\Framework\GraphQl\Exception\GraphQlAuthenticationException;
use Magento\GraphQlNewRelic\Plugin\ReportException;
use Magento\NewRelicReporting\Model\NewRelicWrapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ReportExceptionTest extends TestCase
{
    /**
     * @var NewRelicWrapper|MockObject
     */
    private $newRelicWrapper;

    /**
     * @var ReportException
     */
    private $plugin;

    protected function setUp(): void
    {
        $this->newRelicWrapper = $this->createMock(NewRelicWrapper::class);
        $this->plugin = new ReportException($this->newRelicWrapper);
    }

    public function testServerExceptionIsReported(): void
    {
        $exception = new \RuntimeException('Schema generation failed');
        $this->newRelicWrapper->expects($this->once())
            ->method('reportError')
            ->with($this->identicalTo($exception));

        $this->plugin->beforeCreate($this->createStub(ExceptionFormatter::class), $exception);
    }

    public function testClientSafeExceptionIsNotReported(): void
    {
        $this->newRelicWrapper->expects($this->never())->method('reportError');

        $this->plugin->beforeCreate(
            $this->createStub(ExceptionFormatter::class),
            new GraphQlAuthenticationException(__('The request is allowed for logged in customer'))
        );
    }
}
