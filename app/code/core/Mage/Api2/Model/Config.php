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
 * @category    Mage
 * @package     Mage_Api2
 * @copyright  Copyright (c) 2012 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice api2 config model
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Config extends Varien_Simplexml_Config
{
    /**
     * Node name of resource groups
     */
    const NODE_RESOURCE_GROUPS = 'resource_groups';

    /**
     * Id for config cache
     */
    const CACHE_ID  = 'config_api2';

    /**
     * Tag name for config cache
     */
    const CACHE_TAG = 'CONFIG_API2';

    /**
     * Is resources added to group
     *
     * @var boolean
     */
    protected $_resourcesGrouped = false;

    /**
     * Constructor
     * Initializes XML for this configuration
     * Local cache configuration
     *
     * @param string|Varien_Simplexml_Element|null $sourceData
     */
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);

        $canUserCache = Mage::app()->useCache('config');
        if ($canUserCache) {
            $this->setCacheId(self::CACHE_ID)
                ->setCacheTags(array(self::CACHE_TAG))
                ->setCacheChecksum(null)
                ->setCache(Mage::app()->getCache());

            if ($this->loadCache()) {
                return;
            }
        }

        // Load data of config files api2.xml
        $config = Mage::getConfig()->loadModulesConfiguration('api2.xml');
        $this->setXml($config->getNode('api2'));

        if ($canUserCache) {
            $this->saveCache();
        }
    }

    /**
     * Fetch all routes of the given api type from config files api2.xml
     *
     * @param string $apiType
     * @throws Mage_Api2_Exception
     * @return array
     */
    public function getRoutes($apiType)
    {
        /** @var $helper Mage_Api2_Helper_Data */
        $helper = Mage::helper('Mage_Api2_Helper_Data');
        if (!$helper->isApiTypeSupported($apiType)) {
            throw new Mage_Api2_Exception(sprintf('API type "%s" is not supported', $apiType),
                Mage_Api2_Model_Server::HTTP_BAD_REQUEST);
        }

        $routes = array();
        foreach ($this->getResources() as $resourceKey => $resource) {
            if (!$resource->routes) {
                continue;
            }

            /** @var $routes Varien_Simplexml_Element */
            foreach ($resource->routes->children() as $route) {
                $arguments = array(
                    Mage_Api2_Model_Route_Abstract::PARAM_ROUTE    => (string)$route->route,
                    Mage_Api2_Model_Route_Abstract::PARAM_DEFAULTS => array(
                        'model'       => (string)$resource->model,
                        'type'        => (string)$resourceKey,
                        'action_type' => (string)$route->action_type
                    )
                );

                $routes[] = Mage::getModel('Mage_Api2_Model_Route_' . ucfirst($apiType), $arguments);
            }
        }
        return $routes;
    }

    /**
     * Retrieve all resources from config files api2.xml
     *
     * @return Varien_Simplexml_Element
     */
    public function getResources()
    {
        return $this->getNode('resources')->children();
    }

    /**
     * Retrieve all resources types
     *
     * @return array
     */
    public function getResourcesTypes()
    {
        $list = array();

        foreach ($this->getResources() as $resourceType => $resourceCfg) {
            $list[] = (string) $resourceType;
        }
        return $list;
    }

    /**
     * Retrieve all resource groups from config files api2.xml
     *
     * @return Varien_Simplexml_Element|boolean
     */
    public function getResourceGroups()
    {
        $groups = $this->getXpath('//' . self::NODE_RESOURCE_GROUPS);
        if (!$groups) {
            return false;
        }

        /** @var $groups Varien_Simplexml_Element */
        $groups = $groups[0];

        if (!$this->_resourcesGrouped) {
            /** @var $node Varien_Simplexml_Element */
            foreach ($this->getResources() as $node) {
                $result = $node->xpath('group');
                if (!$result) {
                    continue;
                }
                $groupName = (string) $result[0];
                if ($groupName) {
                    $result = $groups->xpath('.//' . $groupName);
                    if (!$result) {
                        continue;
                    }

                    /** @var $group Varien_Simplexml_Element */
                    $group = $result[0];

                    if (!isset($group->children)) {
                        $children = new Varien_Simplexml_Element('<children />');
                    } else {
                        $children = $group->children;
                    }
                    $node->resource = 1;
                    $children->appendChild($node);
                    $group->appendChild($children);
                }
            }
        }
        return $groups;
    }

    /**
     * Retrieve resource group from config files api2.xml
     *
     * @param string $name
     * @return Mage_Core_Model_Config_Element|boolean
     */
    public function getResourceGroup($name)
    {
        $group = $this->getResourceGroups()->xpath('.//' . $name);
        if (!$group) {
            return false;
        }
        return $group[0];
    }

    /**
     * Retrieve resource by type (node)
     *
     * @param string $node
     * @return Varien_Simplexml_Element|boolean
     */
    public function getResource($node)
    {
        return $this->getNode('resources/' . $node);
    }

    /**
     * Retrieve resource attributes
     *
     * @param string $node
     * @return array
     */
    public function getResourceAttributes($node)
    {
        $attributes = $this->getNode('resources/' . $node . '/attributes');
        return $attributes ? $attributes->asCanonicalArray() : array();
    }

    /**
     * Get excluded attributes of API resource
     *
     * @param string $resource
     * @param string $userType
     * @param string $operation
     * @return array
     */
    public function getResourceExcludedAttributes($resource, $userType, $operation)
    {
        $node = $this->getNode('resources/' . $resource . '/exclude_attributes/' . $userType . '/' . $operation);
        $exclAttributes = array();

        if ($node) {
            foreach ($node->children() as $attribute => $status) {
                if ((string) $status) {
                    $exclAttributes[] = $attribute;
                }
            }
        }
        return $exclAttributes;
    }

    /**
     * Get forced attributes of API resource
     *
     * @param string $resource
     * @param string $userType
     * @return array
     */
    public function getResourceForcedAttributes($resource, $userType)
    {
        $node = $this->getNode('resources/' . $resource . '/force_attributes/' . $userType);
        $forcedAttributes = array();

        if ($node) {
            foreach ($node->children() as $attribute => $status) {
                if ((string) $status) {
                    $forcedAttributes[] = $attribute;
                }
            }
        }
        return $forcedAttributes;
    }

    /**
     * Get included attributes
     *
     * @param string $resource API resource ID
     * @param string $userType API user type
     * @param string $operationType Type of operation: one of Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_... constant
     * @return array
     */
    public function getResourceIncludedAttributes($resource, $userType, $operationType)
    {
        $node = $this->getNode('resources/' . $resource . '/include_attributes/' . $userType . '/' . $operationType);
        $inclAttributes = array();

        if ($node) {
            foreach ($node->children() as $attribute => $status) {
                if ((string) $status) {
                    $inclAttributes[] = $attribute;
                }
            }
        }
        return $inclAttributes;
    }

    /**
     * Get entity only attributes
     *
     * @param string $resource API resource ID
     * @param string $userType API user type
     * @param string $operationType Type of operation: one of Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_... constant
     * @return array
     */
    public function getResourceEntityOnlyAttributes($resource, $userType, $operationType)
    {
        $node = $this->getNode('resources/' . $resource . '/entity_only_attributes/' . $userType . '/' .
            $operationType);
        $entityOnlyAttributes = array();

        if ($node) {
            foreach ($node->children() as $attribute => $status) {
                if ((string) $status) {
                    $entityOnlyAttributes[] = $attribute;
                }
            }
        }
        return $entityOnlyAttributes;
    }

    /**
     * Retrieve resource working model
     *
     * @param string $node
     * @return string
     */
    public function getResourceWorkingModel($node)
    {
        return (string)$this->getNode('resources/' . $node . '/working_model');
    }

    /**
     * Get resource allowed versions sorted in reverse order
     *
     * @param string $node
     * @return array
     * @throws Exception
     */
    public function getVersions($node)
    {
        $element = $this->getNode('resources/' . $node . '/versions');
        if (!$element) {
            throw new Exception(
                sprintf('Resource "%s" does not have node <versions> in config.', htmlspecialchars($node))
            );
        }

        $versions = explode(',', (string)$element);
        if (count(array_filter($versions, 'is_numeric')) != count($versions)) {
            throw new Exception(sprintf('Invalid resource "%s" versions in config.', htmlspecialchars($node)));
        }

        rsort($versions, SORT_NUMERIC);

        return $versions;
    }

    /**
     * Retrieve resource model
     *
     * @param string $node
     * @return string
     */
    public function getResourceModel($node)
    {
        return (string)$this->getNode('resources/' . $node . '/model');
    }

    /**
     * Retrieve API user privileges for specified resource
     *
     * @param string $resource
     * @param string $userType
     * @return array
     */
    public function getResourceUserPrivileges($resource, $userType)
    {
        $attributes = $this->getNode('resources/' . $resource . '/privileges/' . $userType);
        return $attributes ? $attributes->asCanonicalArray() : array();
    }

    /**
     * Retrieve resource subresources
     *
     * @param string $node
     * @return array
     */
    public function getResourceSubresources($node)
    {
        $subresources = $this->getNode('resources/' . $node . '/subresources');
        return $subresources ? $subresources->asCanonicalArray() : array();
    }

    /**
     * Get validation config by validator type
     *
     * @param string $resourceType
     * @param string $validatorType
     * @return array
     */
    public function getValidationConfig($resourceType, $validatorType)
    {
        $config = $this->getNode('resources/' . $resourceType . '/validators/' . $validatorType);
        return $config ? $config->asCanonicalArray() : array();
    }

    /**
     * Get latest version of resource model. If second arg is specified - use it as a limiter
     *
     * @param string $resourceType Resource type
     * @param int $lowerOrEqualsTo OPTIONAL If specified - return version equal or lower to
     * @return int
     */
    public function getResourceLastVersion($resourceType, $lowerOrEqualsTo = null)
    {
        $availVersions = $this->getVersions($resourceType); // already ordered in reverse order
        $useVersion    = reset($availVersions);

        if (null !== $lowerOrEqualsTo) {
            foreach ($availVersions as $availVersion) {
                if ($availVersion <= $lowerOrEqualsTo) {
                    $useVersion = $availVersion;
                    break;
                }
            }
        }
        return (int)$useVersion;
    }

    /**
     * Get route with Mage_Api2_Model_Resource::ACTION_TYPE_ENTITY type
     *
     * @param string $node
     * @return string
     */
    public function getRouteWithEntityTypeAction($node)
    {
        return (string)$this->getNode('resources/' . $node . '/routes/route_entity/route');
    }
}
