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
use Magento\Framework\Service\Data\AbstractExtensibleObject;

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
        $selectedResourceIds = array();
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
     * - 'Magento\Catalog\Service\V2\ProductInterface', true           => catalogProductV2
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
     * - 'Magento\Catalog\Service\V2\ProductInterface', true           => ['CatalogProduct', 'V2']
     * </pre>
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
}
