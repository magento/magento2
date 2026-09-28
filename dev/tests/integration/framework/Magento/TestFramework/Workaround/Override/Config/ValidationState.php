<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Workaround\Override\Config;

use Magento\Framework\Config\ValidationStateInterface;

/**
 * Validation state for tests config.
 */
class ValidationState implements ValidationStateInterface
{
    /**
     * @inheritdoc
     */
    public function isValidationRequired()
    {
        return true;
    }
}
