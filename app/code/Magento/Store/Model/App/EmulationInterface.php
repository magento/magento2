<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Store\Model\App;

use Magento\Framework\App\Area;

/**
 * Environment emulation interface
 */
interface EmulationInterface
{
    /**
     * Start environment emulation of the specified store
     *
     * Function returns information about initial store environment and emulates environment of another store
     *
     * @param integer $storeId
     * @param string $area
     * @param bool $force A true value will ensure that environment is always emulated, regardless of current store
     * @return void
     */
    public function startEnvironmentEmulation($storeId, $area = Area::AREA_FRONTEND, $force = false): void;

    /**
     * Stop environment emulation
     *
     * Function restores initial store environment
     *
     * @return $this
     */
    public function stopEnvironmentEmulation(): self;

    /**
     * Stores current environment info
     *
     * @return void
     */
    public function storeCurrentEnvironmentInfo(): void;

    /**
     * Checks whether the environment is being emulated
     *
     * @return bool
     */
    public function isEnvironmentEmulated(): bool;
}
