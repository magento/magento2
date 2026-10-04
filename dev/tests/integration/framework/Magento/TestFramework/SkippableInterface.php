<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework;

/**
 * Interface for test class wrapper, which allows dynamically skip tests.
 */
interface SkippableInterface
{
    /**
     * Hook method to check config and skip test before start.
     *
     * @before
     * @return void
     */
    public function ___beforeTestRun(): void;
}
