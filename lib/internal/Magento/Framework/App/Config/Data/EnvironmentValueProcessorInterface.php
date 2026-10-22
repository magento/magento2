<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\App\Config\Data;

/**
 * Backend processor that accepts a config value taken verbatim from an environment variable.
 *
 * @api
 */
interface EnvironmentValueProcessorInterface
{
    /**
     * Process a value that was provided through an environment variable instead of the storage
     *
     * @param string|null $value
     * @return string|null
     */
    public function processEnvironmentValue($value);
}
