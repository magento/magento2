<?php
/**
 * ObjectManager config with interception processing
 * 
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Interception\ObjectManager\Config;

use Magento\Framework\Interception\ObjectManager\ConfigInterface;

class Compiled extends \Magento\Framework\ObjectManager\Config\Compiled implements ConfigInterface
{
    /**
     * Interceptors
     *
     * @var array
     */
    private $interceptors = [];

    /**
     * @param array $data
     */
    public function __construct($data)
    {
        $this->interceptors = $data['interceptors'];
        parent::__construct($data);
    }

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
        if ($type === $instanceName) {
            if (isset($this->interceptors[$instanceName])) {
                $type = $this->interceptors[$instanceName];
            }
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

    /**
     * {inheritdoc}
     */
    public function extend(array $configuration)
    {
        parent::extend($configuration);
        $this->interсeptors = $configuration['interceptors'];
    }
}
