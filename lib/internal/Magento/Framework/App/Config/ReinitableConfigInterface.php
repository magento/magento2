<?php
/**
 * Configuration Reinitable Interface
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\App\Config;

/**
 * @api
 * @since 100.0.2
 */
interface ReinitableConfigInterface extends \Magento\Framework\App\Config\MutableScopeConfigInterface
{
    /**
     * Reinitialize config object
     *
     * @return \Magento\Framework\App\Config\ReinitableConfigInterface
     */
    public function reinit();
}
