<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Async;

/**
 * Describes a value that will be available at later time.
 *
 * @api
 */
interface DeferredInterface
{
    /**
     * Wait for and return the value.
     *
     * @return mixed Value.
     * @throws \Throwable When it was impossible to get the value.
     */
    public function get();

    /**
     * Is the process of getting the value is done?
     *
     * @return bool
     */
    public function isDone(): bool;
}
