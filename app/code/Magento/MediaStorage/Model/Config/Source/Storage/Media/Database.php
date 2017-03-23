<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Generate options for media database selection
 */
namespace Magento\MediaStorage\Model\Config\Source\Storage\Media;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Config\ConfigOptionsListConstants;

class Database implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var DeploymentConfig
     */
    protected $deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->deploymentConfig = $deploymentConfig;
    }

    /**
     * Returns list of available resources
     *
     * @return array
     */
    public function toOptionArray()
    {
        $resourceOptions = [];
        $resourceConfig = $this->deploymentConfig->get(ConfigOptionsListConstants::KEY_RESOURCE);
        if (null !== $resourceConfig) {
            foreach (array_keys($resourceConfig) as $resourceName) {
                $resourceOptions[] = ['value' => $resourceName, 'label' => $resourceName];
            }
            sort($resourceOptions);
            reset($resourceOptions);
        }
        return $resourceOptions;
    }
}
