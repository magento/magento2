<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swagger\Model;

use Magento\Framework\App\State;

/**
 * Configuration for Swagger
 */
class Config
{
    /**
     * @param State $state
     * @param bool $enabledInProduction
     */
    public function __construct(
        private readonly State $state,
        private readonly bool $enabledInProduction = false
    ) {
    }

    /**
     * Is Swagger enabled
     *
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->state->getMode() === State::MODE_DEVELOPER || $this->enabledInProduction;
    }
}
