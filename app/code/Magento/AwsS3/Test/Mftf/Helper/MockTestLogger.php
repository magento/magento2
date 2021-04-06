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
class MockTestLogger implements LoggerInterface {

    public function emergency($message, array $context = array())
    {
        throw new \Exception($message);
    }

    public function alert($message, array $context = array())
    {
        // noop
    }

    public function critical($message, array $context = array())
    {
        throw new \Exception($message);
    }

    public function error($message, array $context = array())
    {
        throw new \Exception($message);
    }

    public function warning($message, array $context = array())
    {
        // noop
    }

    public function notice($message, array $context = array())
    {
        // noop
    }

    public function info($message, array $context = array())
    {
        // noop
    }

    public function debug($message, array $context = array())
    {
        // noop
    }

    public function log($level, $message, array $context = array())
    {
        // noop
    }
}
