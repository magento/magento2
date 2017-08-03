<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\ObjectManager;

/**
 * Interface \Magento\Framework\Interception\ObjectManager\ConfigInterface
 *
 * @since 2.0.0
 */
interface ConfigInterface extends \Magento\Framework\ObjectManager\ConfigInterface
{
    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     * @since 2.0.0
     */
    public function setInterceptionConfig(\Magento\Framework\Interception\ConfigInterface $interceptionConfig);

    /**
     * Retrieve instance type without interception processing
     *
     * @param string $instanceName
     * @return string
     * @since 2.0.0
     */
    public function getOriginalInstanceType($instanceName);
}
