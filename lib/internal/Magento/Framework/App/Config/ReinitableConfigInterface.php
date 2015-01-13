<?php
/**
 * Configuration Reinitable Interface
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Config;

interface ReinitableConfigInterface extends \Magento\Framework\App\Config\MutableScopeConfigInterface
{
    /**
     * Reinitialize config object
     *
     * @return \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    public function reinit();
}
