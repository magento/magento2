<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Setup\Model\ConfigOptionsList;

use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerFactoryInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\RequestLoggerInterface;
use Magento\Framework\App\Backpressure\SlidingWindow\ValkeyRequestLogger;
use Magento\Framework\Setup\Option\SelectConfigOption;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Integration tests for backpressure logger setup options (ACP2E-4899 / ACQE-9901).
 *
 * Verifies Valkey is registered via DI as a valid backpressure logger for Order Rate Limit
 * and that the object manager can instantiate the Valkey request logger implementation.
 */
class BackpressureLoggerTest extends TestCase
{
    /**
     * @var BackpressureLogger
     */
    private BackpressureLogger $backpressureLogger;

    /**
     * @var RequestLoggerFactoryInterface
     */
    private RequestLoggerFactoryInterface $requestLoggerFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->backpressureLogger = $objectManager->get(BackpressureLogger::class);
        $this->requestLoggerFactory = $objectManager->get(RequestLoggerFactoryInterface::class);
    }

    /**
     * BackpressureLogger must expose valkey as a valid logger type via DI configuration.
     *
     * @return void
     */
    public function testBackpressureLoggerAcceptsValkeyFromDiConfiguration(): void
    {
        $options = $this->backpressureLogger->getOptions();
        $this->assertInstanceOf(SelectConfigOption::class, $options[0]);

        $selectOptions = $options[0]->getSelectOptions();
        $this->assertArrayHasKey('valkey', $selectOptions);
        $this->assertSame('valkey', $selectOptions['valkey']);
    }

    /**
     * Request logger factory must resolve valkey to ValkeyRequestLogger for runtime order rate limiting.
     *
     * @return void
     */
    public function testRequestLoggerFactoryCreatesValkeyRequestLogger(): void
    {
        $logger = $this->requestLoggerFactory->create('valkey');

        $this->assertInstanceOf(ValkeyRequestLogger::class, $logger);
        $this->assertInstanceOf(RequestLoggerInterface::class, $logger);
    }
}
