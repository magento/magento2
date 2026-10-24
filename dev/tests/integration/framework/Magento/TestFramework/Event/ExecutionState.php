<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Event;

class ExecutionState
{
    /**
     * @var array
     */
    private array $data = [];

    /**
     * Register failure during preparation phase.
     *
     * @param string $test
     * @param \Throwable $exception
     * @return void
     */
    public function registerPreparationFailure(string $test, \Throwable $exception): void
    {
        $this->data[$test]['exception'] = $exception;
    }

    /**
     * Pop failure registered during preparation phase.
     *
     * @param string $test
     * @return \Throwable|null
     */
    public function popPreparationFailure(string $test): ?\Throwable
    {
        $exception = $this->data[$test]['exception'] ?? null;
        if ($exception) {
            unset($this->data[$test]['exception']);
        }

        return $exception;
    }

    /**
     * Clear stored test data.
     *
     * @param string $test
     * @return void
     */
    public function clearTestData(string $test): void
    {
        unset($this->data[$test]);
    }
}
