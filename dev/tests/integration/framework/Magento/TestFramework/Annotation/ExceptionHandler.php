<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use PHPUnit\Framework\Exception;
use ReflectionClass;
use ReflectionException;

class ExceptionHandler
{
    /**
     * Format exception message and throws PHPUnit\Framework\Exception
     *
     * @param string $message
     * @param string $testClass
     * @param string|null $testMethod
     * @param \Throwable|null $previous
     * @return void
     */
    public static function handle(
        string $message,
        string $testClass,
        string $testMethod = null,
        \Throwable $previous = null
    ): void {
        try {
            $reflected = new ReflectionClass($testClass);
        } catch (ReflectionException $e) {
            throw new Exception(
                $e->getMessage(),
                (int) $e->getCode(),
                $e
            );
        }

        $name = $testMethod;

        if ($name && $reflected->hasMethod($name)) {
            try {
                $reflected = $reflected->getMethod($name);
            } catch (ReflectionException $e) {
                throw new Exception(
                    $e->getMessage(),
                    (int) $e->getCode(),
                    $e
                );
            }
        }

        $location = sprintf(
            "%s(%d): %s->%s()",
            $reflected->getFileName(),
            $reflected->getStartLine(),
            $testClass,
            $testMethod
        );

        $summary = '';
        if ($previous) {
            $exception = $previous;
            do {
                $summary .= PHP_EOL
                    . PHP_EOL
                    . 'Caused By: '
                    . $exception->getMessage()
                    . PHP_EOL
                    . $exception->getTraceAsString();
            } while ($exception = $exception->getPrevious());
        }
        throw new Exception(
            sprintf(
                "%s\n#0 %s%s",
                $message,
                $location,
                $summary
            ),
            0,
            $previous
        );
    }
}
