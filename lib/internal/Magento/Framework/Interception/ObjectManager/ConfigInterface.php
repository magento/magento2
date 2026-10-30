<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\ObjectManager;

/**
 * Interface \Magento\Framework\Interception\ObjectManager\ConfigInterface
 *
 * @api
 */
interface ConfigInterface extends \Magento\Framework\ObjectManager\ConfigInterface
{
    /**
     * Set Interception config
     *
     * @param \Magento\Framework\Interception\ConfigInterface $interceptionConfig
     * @return void
     */
    public function setInterceptionConfig(\Magento\Framework\Interception\ConfigInterface $interceptionConfig);

    /**
     * Retrieve instance type without interception processing
     *
     * @param string $instanceName
     * @return string
     */
    public function getOriginalInstanceType($instanceName);
}
