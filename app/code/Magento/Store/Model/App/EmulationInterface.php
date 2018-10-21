<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\Model\App;

use Magento\Framework\App\Area;

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
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function startEnvironmentEmulation($storeId, $area = Area::AREA_FRONTEND, $force = false);

    /**
     * Stop environment emulation
     *
     * Function restores initial store environment
     *
     * @return $this
     */
    public function stopEnvironmentEmulation();

    /**
     * Stores current environment info
     *
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function storeCurrentEnvironmentInfo();
}
