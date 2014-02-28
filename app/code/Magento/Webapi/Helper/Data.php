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

namespace Magento\Webapi\Helper;

use Magento\Integration\Controller\Adminhtml\Integration as IntegrationController;

class Data extends \Magento\App\Helper\AbstractHelper
{
    /** @var \Magento\Registry */
    protected $_registry;

    /**
     * @param \Magento\App\Helper\Context $context
     * @param \Magento\Registry $registry
     */
    public function __construct(
        \Magento\App\Helper\Context $context,
        \Magento\Registry $registry
    ) {
        $this->_registry = $registry;
        parent::__construct($context);
    }

    /**
     * @return array
     */
    public function getSelectedResources()
    {
        $selectedResourceIds = array();
        $currentIntegration = $this->_registry->registry(IntegrationController::REGISTRY_KEY_CURRENT_INTEGRATION);
        if ($currentIntegration
            && isset($currentIntegration['resource']) && is_array($currentIntegration['resource'])
        ) {
            $selectedResourceIds = $currentIntegration['resource'];
        }
        return $selectedResourceIds;
    }

    /**
     * Translate service interface name into service name.
     * Example:
     * <pre>
     * - \Magento\Customer\Service\CustomerV1Interface         => customer          // $preserveVersion == false
     * - \Magento\Customer\Service\Customer\AddressV1Interface => customerAddressV1 // $preserveVersion == true
     * - \Magento\Catalog\Service\ProductV2Interface           => catalogProductV2  // $preserveVersion == true
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
     * Examples of input/output pairs: <br/>
     * - 'Magento\Customer\Service\Customer\AddressV1Interface' => array('Customer', 'Address', 'V1') <br/>
     * - 'Vendor\Customer\Service\Customer\AddressV1Interface' => array('VendorCustomer', 'Address', 'V1) <br/>
     * - 'Magento\Catalog\Service\ProductV2Interface' => array('CatalogProduct', 'V2')
     *
     * @param string $className
     * @param bool $preserveVersion Should version be preserved during class name conversion into service name
     * @return string[]
     * @throws \InvalidArgumentException When class is not valid API service.
     */
    public function getServiceNameParts($className, $preserveVersion = false)
    {
        if (preg_match(\Magento\Webapi\Model\Config::SERVICE_CLASS_PATTERN, $className, $matches)) {
            $moduleNamespace = $matches[1];
            $moduleName = $matches[2];
            $moduleNamespace = ($moduleNamespace == 'Magento') ? '' : $moduleNamespace;
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
        }
        throw new \InvalidArgumentException(sprintf('The service interface name "%s" is invalid.', $className));
    }

    /**
     * Convert DTO getter name into field name.
     *
     * @param string $getterName
     * @return string
     */
    public function dtoGetterNameToFieldName($getterName)
    {
        if ((strpos($getterName, 'get') === 0)) {
            /** Remove 'get' prefix and make the first letter lower case */
            $fieldName = substr($getterName, strlen('get'));
        } else {
            /** If methods are with 'is' or 'has' prefix */
            $fieldName = $getterName;
        }
        return lcfirst($fieldName);
    }

    /**
     * Convert DTO field name into setter name.
     *
     * @param string $fieldName
     * @return string
     */
    public function dtoFieldNameToSetterName($fieldName)
    {
        return 'set' . ucfirst($fieldName);
    }
}
