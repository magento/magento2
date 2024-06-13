<?php
/**
 * ObjectManager config with interception processing
 *
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Interception\ObjectManager\Config;

use Magento\Framework\Interception\ObjectManager\ConfigInterface;

class Compiled extends \Magento\Framework\ObjectManager\Config\Compiled implements ConfigInterface
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
