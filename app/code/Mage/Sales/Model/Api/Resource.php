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
 * @package     Mage_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sale api resource abstract
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Api_Resource extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Default ignored attribute codes per entity type
     *
     * @var array
     */
    protected $_ignoredAttributeCodes = array(
        'global'    =>  array('entity_id', 'attribute_set_id', 'entity_type_id')
    );

    /**
     * Attributes map array per entity type
     *
     * @var google
     */
    protected $_attributesMap = array(
        'global'    => array()
    );

    /** @var Mage_Api_Helper_Data */
    protected $_apiHelper;

    /**
     * Initialize dependencies.
     *
     * @param Mage_Api_Helper_Data $apiHelper
     */
    public function __construct(Mage_Api_Helper_Data $apiHelper)
    {
        $this->_apiHelper = $apiHelper;
    }

    /**
     * Update attributes for entity
     *
     * @param array $data
     * @param Mage_Core_Model_Abstract $object
     * @param array $attributes
     * @return Mage_Sales_Model_Api_Resource
     */
    protected function _updateAttributes($data, $object, $type,  array $attributes = null)
    {

        foreach ($data as $attribute=>$value) {
            if ($this->_apiHelper->isAttributeAllowed($attribute, $type, $this->_ignoredAttributeCodes, $attributes)) {
                $object->setData($attribute, $value);
            }
        }

        return $this;
    }

    /**
     * Retrieve entity attributes values
     *
     * @param Mage_Core_Model_Abstract $object
     * @param array $attributes
     * @return Mage_Sales_Model_Api_Resource
     */
    protected function _getAttributes($object, $type, array $attributes = null)
    {
        $result = array();

        if (!is_object($object)) {
            return $result;
        }

        foreach ($object->getData() as $attribute=>$value) {
            if ($this->_apiHelper->isAttributeAllowed($attribute, $type, $this->_ignoredAttributeCodes, $attributes)) {
                $result[$attribute] = $value;
            }
        }

        if (isset($this->_attributesMap['global'])) {
            foreach ($this->_attributesMap['global'] as $alias=>$attributeCode) {
                $result[$alias] = $object->getData($attributeCode);
            }
        }

        if (isset($this->_attributesMap[$type])) {
            foreach ($this->_attributesMap[$type] as $alias=>$attributeCode) {
                $result[$alias] = $object->getData($attributeCode);
            }
        }

        return $result;
    }
} // Class Mage_Sales_Model_Api_Resource End
