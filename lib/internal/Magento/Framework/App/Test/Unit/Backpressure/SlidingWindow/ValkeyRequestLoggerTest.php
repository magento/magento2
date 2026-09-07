<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Test\Unit\Backpressure\SlidingWindow;

use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\ValkeyRequestLogger;
use PHPUnit\Framework\TestCase;

class ValkeyRequestLoggerTest extends TestCase
{
    public function testImplementsRequestLoggerInterface(): void
    {
        $this->assertInstanceOf(
            RequestLoggerInterface::class,
            $this->createMock(ValkeyRequestLogger::class)
        );
    }
}
