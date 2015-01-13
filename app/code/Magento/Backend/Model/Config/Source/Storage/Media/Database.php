<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Generate options for media database selection
 */
namespace Magento\Backend\Model\Config\Source\Storage\Media;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\DeploymentConfig\ResourceConfig;

class Database implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var DeploymentConfig
     */
    protected $_deploymentConfig;

    /**
     * @param DeploymentConfig $deploymentConfig
     */
    public function __construct(DeploymentConfig $deploymentConfig)
    {
        $this->_deploymentConfig = $deploymentConfig;
    }

    /**
     * Returns list of available resources
     *
     * @return array
     */
    public function toOptionArray()
    {
        $resourceOptions = [];
        $resourceInfo = $this->_deploymentConfig->getSegment(ResourceConfig::CONFIG_KEY);
        if (null !== $resourceInfo) {
            $resourceConfig = new ResourceConfig($resourceInfo);
            foreach (array_keys($resourceConfig->getData()) as $resourceName) {
                $resourceOptions[] = ['value' => $resourceName, 'label' => $resourceName];
            }
            sort($resourceOptions);
            reset($resourceOptions);
        }
        return $resourceOptions;
    }
}
