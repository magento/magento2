<?php
/**
 * ObjectManager config with interception processing
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\ObjectManager\Config;

use Magento\Framework\Interception\ObjectManager\ConfigInterface;

/**
 * Class \Magento\Framework\Interception\ObjectManager\Config\Compiled
 *
 * @since 2.0.0
 */
class Compiled extends \Magento\Framework\ObjectManager\Config\Compiled implements ConfigInterface
{
    /**
     * @var \Magento\Framework\Interception\ConfigInterface
     * @since 2.0.0
     */
    protected $interceptionConfig;

    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getOriginalInstanceType($instanceName)
    {
        return parent::getInstanceType($instanceName);
    }
}
