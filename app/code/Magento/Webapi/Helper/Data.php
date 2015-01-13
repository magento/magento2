<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Webapi\Helper;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;

/**
 * Class Data
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /** @var \Magento\Framework\Registry */
    protected $_registry;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(\Magento\Framework\App\Helper\Context $context, \Magento\Framework\Registry $registry)
    {
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getSelectedResources()
    {
        $selectedResourceIds = [];
        $currentIntegration = $this->_registry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        if ($currentIntegration && isset($currentIntegration['resource']) && is_array($currentIntegration['resource'])
        ) {
            $selectedResourceIds = $currentIntegration['resource'];
        }
        return $selectedResourceIds;
    }

    /**
     * Translate service interface name into service name.
     * Example:
     * <pre>
     * - 'Magento\Customer\Service\V1\CustomerAccountInterface', false => customerCustomerAccount
     * - 'Magento\Customer\Service\V1\CustomerAddressInterface', true  => customerCustomerAddressV1
     * </pre>
     *
     * @param string $interfaceName
     * @param bool $preserveVersion Should version be preserved during interface name conversion into service name
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getServiceName($interfaceName, $preserveVersion = true)
    {
        $serviceNameParts = $this->getServiceNameParts($interfaceName, $preserveVersion);
        return lcfirst(implode('', $serviceNameParts));
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
