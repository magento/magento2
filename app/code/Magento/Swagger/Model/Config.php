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
     * @var State
     */
    private $state;

    /**
     * @var bool
     */
    private $enabledInProduction;

    /**
     * @param State $state
     * @param bool $enabledInProduction
     */
    public function __construct(State $state, bool $enabledInProduction = false)
    {
        $this->state = $state;
        $this->enabledInProduction = $enabledInProduction;
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
