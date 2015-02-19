<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Model;

use Magento\Framework\App\Cache\Type\Webapi as WebapiCache;
use Magento\Webapi\Model\Config\Reader;

/**
 * Web API Config Model.
 *
 * This is a parent class for storing information about service configuration.
 */
class Config
{
    const CACHE_ID = 'webapi';

    /**
     * Pattern for Web API interface name.
     */
    const SERVICE_CLASS_PATTERN = '/^(.+?)\\\\(.+?)\\\\Service\\\\(V\d+)+(\\\\.+)Interface$/';

    const API_PATTERN = '/^(.+?)\\\\(.+?)\\\\Api(\\\\.+)Interface$/';

    /**
     * @var WebapiCache
     */
    protected $cache;

    /**
     * @var Reader
     */
    protected $configReader;

    /**
     * @var array
     */
    protected $services;

    /**
     * Initialize dependencies.
     *
     * @param WebapiCache $cache
     * @param Reader $configReader
     */
    public function __construct(WebapiCache $cache, Reader $configReader)
    {
        $this->cache = $cache;
        $this->configReader = $configReader;
    }

    /**
     * Return services loaded from cache if enabled or from files merged previously
     *
     * @return array
     */
    public function getServices()
    {
        if (null === $this->services) {
            $services = $this->cache->load(self::CACHE_ID);
            if ($services && is_string($services)) {
                $this->services = unserialize($services);
            } else {
                $this->services = $this->configReader->read();
                $this->cache->save(serialize($this->services), self::CACHE_ID);
            }
        }
        return $this->services;
    }

    /**
     * Identify the list of service name parts including sub-services using class name.
     *
     * Examples of input/output pairs:
     * <pre>
     * - 'Magento\Customer\Service\V1\CustomerAccountInterface', false => ['CustomerCustomerAccount']
     * - 'Vendor\Customer\Service\V1\Customer\AddressInterface', true  => ['VendorCustomer', 'Address', 'V1']
     * </pre>
     *
     * @param string $className
     * @param bool $preserveVersion Should version be preserved during class name conversion into service name
     * @return string[]
     * @throws \InvalidArgumentException When class is not valid API service.
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getServiceNameParts($className, $preserveVersion = false)
    {
        if (!preg_match(\Magento\Webapi\Model\Config::SERVICE_CLASS_PATTERN, $className, $matches)) {
            $apiClassPattern = "#^(.+?)\\\\(.+?)\\\\Api\\\\(.+?)(Interface)?$#";
            preg_match($apiClassPattern, $className, $matches);
        }

        if (!empty($matches)) {
            $moduleNamespace = $matches[1];
            $moduleName = $matches[2];
            $moduleNamespace = ($moduleNamespace == 'Magento') ? '' : $moduleNamespace;
            if ($matches[4] === 'Interface') {
                $matches[4] = $matches[3];
                $matches[3] = 'V1';
            }
            $serviceNameParts = explode('\\', trim($matches[4], '\\'));
            if ($moduleName == $serviceNameParts[0]) {
                /** Avoid duplication of words in service name */
                $moduleName = '';
            }
            $parentServiceName = $moduleNamespace . $moduleName . array_shift($serviceNameParts);
            array_unshift($serviceNameParts, $parentServiceName);
            if ($preserveVersion) {
                $serviceVersion = $matches[3];
                $serviceNameParts[] = $serviceVersion;
            }
            return $serviceNameParts;
        } elseif (preg_match(\Magento\Webapi\Model\Config::API_PATTERN, $className, $matches)) {
            $moduleNamespace = $matches[1];
            $moduleName = $matches[2];
            $moduleNamespace = ($moduleNamespace == 'Magento') ? '' : $moduleNamespace;
            $serviceNameParts = explode('\\', trim($matches[3], '\\'));
            if ($moduleName == $serviceNameParts[0]) {
                /** Avoid duplication of words in service name */
                $moduleName = '';
            }
            $parentServiceName = $moduleNamespace . $moduleName . array_shift($serviceNameParts);
            array_unshift($serviceNameParts, $parentServiceName);
            //Add temporary dummy version
            $serviceNameParts[] = 'V1';
            return $serviceNameParts;
        }

        throw new \InvalidArgumentException(sprintf('The service interface name "%s" is invalid.', $className));
    }
}
