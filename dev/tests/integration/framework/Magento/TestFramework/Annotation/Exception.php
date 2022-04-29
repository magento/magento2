<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Annotation;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
use Throwable;

class Exception extends \PHPUnit\Framework\Exception
{
    /**
     * @param string $message
     * @param Throwable|null $previous
     * @param int $code
     */
    public function __construct(
        string $message,
        Throwable $previous = null,
        int $code = 0
    ) {
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
        parent::__construct(
            sprintf(
                "%s%s",
                $message,
                $summary
            ),
            $code,
            $previous
        );
    }
}
