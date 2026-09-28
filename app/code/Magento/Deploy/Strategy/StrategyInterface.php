<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Deploy\Strategy;

use Magento\Deploy\Package\Package;

/**
 * Deployment strategy interface
 *
 * @api
 */
interface StrategyInterface
{
    /**
     * Execute deployment of static files
     *
     * @param array $options
     * @return Package[]
     */
    public function deploy(array $options);
}
