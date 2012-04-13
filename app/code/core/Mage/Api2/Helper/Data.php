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
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Webservice API2 data helper
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Request interpret adapters
     */
    const XML_PATH_API2_REQUEST_INTERPRETERS = 'global/api2/request/interpreters';

    /**
     * Response render adapters
     */
    const XML_PATH_API2_RESPONSE_RENDERS     = 'global/api2/response/renders';

    /**#@+
     * Config paths
     */
    const XML_PATH_AUTH_ADAPTERS = 'global/api2/auth_adapters';
    const XML_PATH_USER_TYPES    = 'global/api2/user_types';
    /**#@- */

    /**
     * Compare order to be used in adapters list sort
     *
     * @param int $a
     * @param int $b
     * @return int
     */
    protected static function _compareOrder($a, $b)
    {
        if ($a['order'] == $b['order']) {
            return 0;
        }
        return ($a['order'] < $b['order']) ? -1 : 1;
    }

    /**
     * Retrieve Auth adapters info from configuration file as array
     *
     * @param bool $enabledOnly
     * @return array
     */
    public function getAuthAdapters($enabledOnly = false)
    {
        $adapters = Mage::getConfig()->getNode(self::XML_PATH_AUTH_ADAPTERS);

        if (!$adapters) {
            return array();
        }
        $adapters = $adapters->asArray();

        if ($enabledOnly) {
            foreach ($adapters as $adapter) {
                if (empty($adapter['enabled'])) {
                    unset($adapters);
                }
            }
            $adapters = (array) $adapters;
        }
        uasort($adapters, array('Mage_Api2_Helper_Data', '_compareOrder'));

        return $adapters;
    }

    /**
     * Retrieve enabled user types in form of user type => user model pairs
     *
     * @return array
     */
    public function getUserTypes()
    {
        $userModels = array();
        $types = Mage::getConfig()->getNode(self::XML_PATH_USER_TYPES);

        if ($types) {
            foreach ($types->asArray() as $type => $params) {
                if (!empty($params['allowed'])) {
                    $userModels[$type] = $params['model'];
                }
            }
        }
        return $userModels;
    }

    /**
     * Get interpreter type for Request body according to Content-type HTTP header
     *
     * @return array
     */
    public function getRequestInterpreterAdapters()
    {
        return (array) Mage::app()->getConfig()->getNode(self::XML_PATH_API2_REQUEST_INTERPRETERS);
    }

    /**
     * Get interpreter type for Request body according to Content-type HTTP header
     *
     * @return array
     */
    public function getResponseRenderAdapters()
    {
        return (array) Mage::app()->getConfig()->getNode(self::XML_PATH_API2_RESPONSE_RENDERS);
    }

    /**
     * Check API type support
     *
     * @param string $type
     * @return bool
     */
    public function isApiTypeSupported($type)
    {
        return in_array($type, Mage_Api2_Model_Server::getApiTypes());
    }

    /**
     * Get allowed attributes of a rule
     *
     * @param string $userType
     * @param string $resourceId
     * @param string $operation One of Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_... constant
     * @return array
     */
    public function getAllowedAttributes($userType, $resourceId, $operation)
    {
        /** @var $resource Mage_Api2_Model_Resource_Acl_Filter_Attribute */
        $resource = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Filter_Attribute');

        $attributes = $resource->getAllowedAttributes($userType, $resourceId, $operation);

        return ($attributes === false || $attributes === null ? array() : explode(',', $attributes));
    }

    /**
     * Check if ALL attributes are allowed
     *
     * @param string $userType
     * @return bool
     */
    public function isAllAttributesAllowed($userType)
    {
        /** @var $resource Mage_Api2_Model_Resource_Acl_Filter_Attribute */
        $resource = Mage::getResourceModel('Mage_Api2_Model_Resource_Acl_Filter_Attribute');

        return $resource->isAllAttributesAllowed($userType);
    }

    /**
     * Get operation type for specified operation
     *
     * @param string $operation One of Mage_Api2_Model_Resource::OPERATION_... constant
     * @return string One of Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_... constant
     * @throws Exception
     */
    public function getTypeOfOperation($operation)
    {
        if (Mage_Api2_Model_Resource::OPERATION_RETRIEVE === $operation) {
            return Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ;
        } elseif (Mage_Api2_Model_Resource::OPERATION_CREATE === $operation) {
            return Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_WRITE;
        } elseif (Mage_Api2_Model_Resource::OPERATION_UPDATE === $operation) {
            return Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_WRITE;
        } else {
            throw new Exception('Can not determine operation type');
        }
    }
}
