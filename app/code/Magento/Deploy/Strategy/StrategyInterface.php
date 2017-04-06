<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Deploy\Strategy;

use Magento\Deploy\Package\Package;

/**
 * Deployment strategy interface
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
