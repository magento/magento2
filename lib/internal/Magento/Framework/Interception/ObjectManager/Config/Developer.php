<?php
/**
 * ObjectManager config with interception processing
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\ObjectManager\Config;

use Magento\Framework\Interception\ObjectManager\ConfigInterface;

class Developer extends \Magento\Framework\ObjectManager\Config\Config implements ConfigInterface
{
    /**
     * @var \Magento\Framework\Interception\ConfigInterface
     */
    protected $interceptionConfig;

    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     */
    public function setInterceptionConfig(\Magento\Framework\Interception\ConfigInterface $interceptionConfig)
    {
        $this->interceptionConfig = $interceptionConfig;
    }

    /**
     * Retrieve instance type with interception processing
     *
     * @param string $instanceName
     * @return string
     */
    public function getInstanceType($instanceName)
    {
        $type = parent::getInstanceType($instanceName);
        if ($this->interceptionConfig && $this->interceptionConfig->hasPlugins($instanceName)) {
            return $type . '\\Interceptor';
        }
        return $type;
    }

    /**
     * Retrieve instance type without interception processing
     *
     * @param string $instanceName
     * @return string
     */
    public function getOriginalInstanceType($instanceName)
    {
        return parent::getInstanceType($instanceName);
    }
}
