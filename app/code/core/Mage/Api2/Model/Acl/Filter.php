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
 * API ACL filter
 *
 * @category   Mage
 * @package    Mage_Api2
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Api2_Model_Acl_Filter
{
    /**
     * Attributes allowed for use
     *
     * @var array
     */
    protected $_allowedAttributes;

    /**
     * A list of attributes to be included into output
     *
     * @var array
     */
    protected $_attributesToInclude;

    /**
     * Associated resource model
     *
     * @var Mage_Api2_Model_Resource
     */
    protected $_resource;

    /**
     * Object constructor
     *
     * @param Mage_Api2_Model_Resource $resource
     */
    public function __construct(Mage_Api2_Model_Resource $resource)
    {
        $this->_resource = $resource;
    }

    /**
     * Return only the data which keys are allowed
     *
     * @param array $allowedAttributes List of attributes available to use
     * @param array $data Associative array attribute to value
     * @return array
     */
    protected function _filter(array $allowedAttributes, array $data)
    {
        foreach ($data as $attribute => $value) {
            if (!in_array($attribute, $allowedAttributes)) {
                unset($data[$attribute]);
            }
        }
        return $data;
    }

    /**
     * Strip attributes in of collection items
     *
     * @param array $items
     * @return array
     */
    public function collectionIn($items)
    {
        foreach ($items as &$data) {
            $data = is_array($data) ? $this->in($data) : array();
        }
        return $items;
    }

    /**
     * Strip attributes out of collection items
     *
     * @param array $items
     * @return array
     */
    public function collectionOut($items)
    {
        foreach ($items as &$data) {
            $data = $this->out($data);
        }
        return $items;
    }

    /**
     * Fetch array of allowed attributes for given resource type, operation and user type.
     *
     * @param string $operationType OPTIONAL One of Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_... constant
     * @return array
     */
    public function getAllowedAttributes($operationType = null)
    {
        if (null === $this->_allowedAttributes) {
            /** @var $helper Mage_Api2_Helper_Data */
            $helper = Mage::helper('Mage_Api2_Helper_Data');

            if (null === $operationType) {
                $operationType = $helper->getTypeOfOperation($this->_resource->getOperation());
            }
            if ($helper->isAllAttributesAllowed($this->_resource->getUserType())) {
                $this->_allowedAttributes = array_keys($this->_resource->getAvailableAttributes(
                    $this->_resource->getUserType(), $operationType
                ));
            } else {
                $this->_allowedAttributes = $helper->getAllowedAttributes(
                    $this->_resource->getUserType(), $this->_resource->getResourceType(), $operationType
                );
            }
            // force attributes to be no filtered
            foreach ($this->_resource->getForcedAttributes() as $forcedAttr) {
                if (!in_array($forcedAttr, $this->_allowedAttributes)) {
                    $this->_allowedAttributes[] = $forcedAttr;
                }
            }
        }
        return $this->_allowedAttributes;
    }

    /**
     * Retrieve a list of attributes to be included in output based on available and requested attributes
     *
     * @return array
     */
    public function getAttributesToInclude()
    {
        if (null === $this->_attributesToInclude) {
            $allowedAttrs   = $this->getAllowedAttributes(Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_READ);
            $requestedAttrs = $this->_resource->getRequest()->getRequestedAttributes();

            if ($requestedAttrs) {
                foreach ($allowedAttrs as $allowedAttr) {
                    if (in_array($allowedAttr, $requestedAttrs)) {
                        $this->_attributesToInclude[] = $allowedAttr;
                    }
                }
            } else {
                $this->_attributesToInclude = $allowedAttrs;
            }
        }
        return $this->_attributesToInclude;
    }

    /**
     * Filter data for write operations
     *
     * @param array $requestData
     * @return array
     */
    public function in(array $requestData)
    {
        $allowedAttributes = $this->getAllowedAttributes(Mage_Api2_Model_Resource::OPERATION_ATTRIBUTE_WRITE);

        return $this->_filter($allowedAttributes, $requestData);
    }

    /**
     * Filter data before output
     *
     * @param array $retrievedData
     * @return array
     */
    public function out(array $retrievedData)
    {
        return $this->_filter($this->getAttributesToInclude(), $retrievedData);
    }
}
