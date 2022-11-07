<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\Api\Data;

/**
 * Emulation Interface
 *
 * @api
 */
interface EmulationInterface
{
    /**
     * Get initialEnvironmentInfo
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getInitialEnvironmentInfo();
}
