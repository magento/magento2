<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
     * @return string|null
     */
    public function getServiceLabel($serviceName)
    {
        if (isset($this->_servicesConfig[$serviceName]['label'])) {
            return __($this->_servicesConfig[$serviceName]['label']);
        }
        return null;
    }
}
