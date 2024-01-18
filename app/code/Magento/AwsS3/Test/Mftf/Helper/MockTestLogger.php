<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AwsS3\Test\Mftf\Helper;

use Psr\Log\LoggerInterface;

/**
 * Mocked logger for using the AwsS3 driver in testing
 *
 * Ignores most log messages but throws errors on error/critical/emergency logs so tests will fail
 */
class MockTestLogger implements LoggerInterface
{
    /**
     * @param $message
     * @param array $context
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function emergency($message, array $context = []): void
    {
        throw new \Exception($message);
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function alert($message, array $context = []): void
    {
        // noop
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function critical($message, array $context = []): void
    {
        throw new \Exception($message);
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function error($message, array $context = []): void
    {
        throw new \Exception($message);
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function warning($message, array $context = []): void
    {
        // noop
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function notice($message, array $context = []): void
    {
        // noop
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function info($message, array $context = []): void
    {
        // noop
    }

    /**
     * @param $message
     * @param array $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function debug($message, array $context = []): void
    {
        // noop
    }

    /**
     * @param $level
     * @param $message
     * @param array $context
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function log($level, $message, array $context = []): void
    {
        // noop
    }
}
