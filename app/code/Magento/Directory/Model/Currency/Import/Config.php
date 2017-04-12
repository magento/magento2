<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Currency\Import;

class Config
{
    /**
     * @var array
     */
    private $_servicesConfig;

    /**
     * Validate format of services configuration array
     *
     * @param array $servicesConfig
     * @throws \InvalidArgumentException
     */
    public function __construct(array $servicesConfig)
    {
        foreach ($servicesConfig as $serviceName => $serviceInfo) {
            if (!is_string($serviceName) || empty($serviceName)) {
                throw new \InvalidArgumentException('Name for a currency import service has to be specified.');
            }
            if (empty($serviceInfo['class'])) {
                throw new \InvalidArgumentException('Class for a currency import service has to be specified.');
            }
            if (empty($serviceInfo['label'])) {
                throw new \InvalidArgumentException('Label for a currency import service has to be specified.');
            }
        }
        $this->_servicesConfig = $servicesConfig;
    }

    /**
     * Retrieve unique names of all available currency import services
     *
     * @return array
     */
    public function getAvailableServices()
    {
        return array_keys($this->_servicesConfig);
    }

    /**
     * Retrieve name of a class that corresponds to service name
     *
     * @param string $serviceName
     * @return string|null
     */
    public function getServiceClass($serviceName)
    {
        if (isset($this->_servicesConfig[$serviceName]['class'])) {
            return $this->_servicesConfig[$serviceName]['class'];
        }
        return null;
    }

    /**
     * Retrieve already translated label that corresponds to service name
     *
     * @param string $serviceName
     * @return \Magento\Framework\Phrase|null
     */
    public function getServiceLabel($serviceName)
    {
        if (isset($this->_servicesConfig[$serviceName]['label'])) {
            return __($this->_servicesConfig[$serviceName]['label']);
        }
        return null;
    }
}
