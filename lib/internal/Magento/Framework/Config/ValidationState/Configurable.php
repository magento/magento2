<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Config\ValidationState;

use Magento\Framework\Config\ValidationStateInterface;

/**
 * A configurable validation state
 */
class Configurable implements ValidationStateInterface
{
    /**
     * @var bool
     */
    private $required;

    /**
     * @param bool $required
     */
    public function __construct(bool $required)
    {
        $this->required = $required;
    }

    /**
     * @inheritdoc
     */
    public function isValidationRequired(): bool
    {
        return $this->required;
    }
}
