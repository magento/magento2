<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use PHPUnit\Event\Code\ThrowableBuilder;
use PHPUnit\Framework\Exception;
use PHPUnit\Framework\TestCase;

class ExceptionHandler
{
    /**
     * Throws \PHPUnit\Framework\Exception and fail the test if provided.
     *
     * @param string $message
     * @param \Throwable|null $previous
     * @param TestCase|null $test
     * @return never
     * @throws Exception
     */
    public static function handle(
        string $message,
        ?\Throwable $previous = null,
        ?TestCase $test = null
    ): never {
        if (!$test) {
            throw new Exception($message, 0, $previous);
        }

        if ($previous) {
            $throwable = ThrowableBuilder::from($previous);
            $message .= PHP_EOL . 'Caused by' . PHP_EOL . $throwable->asString();
        }
        $test::fail($message);
    }
}
