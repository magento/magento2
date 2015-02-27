<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Module\ModuleList;

/**
 * Factory for Deployment configuration segment for modules
 */
class DeploymentConfigFactory
{
    /**
     * Factory method for Deployment Config object
     *
     * @param array $data
     * @return DeploymentConfig
     */
    public function create(array $data)
    {
        return new DeploymentConfig($data);
    }
}
