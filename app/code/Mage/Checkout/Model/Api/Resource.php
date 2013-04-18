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
 * @package     Mage_Checkout
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Checkout api resource
 *
 * @category   Mage
 * @package    Mage_Checkout
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Checkout_Model_Api_Resource extends Mage_Api_Model_Resource_Abstract
{
    /**
     * Attributes map array per entity type
     *
     * @var array
     */
    protected $_attributesMap = array(
        'global' => array(),
    );

    /**
     * Default ignored attribute codes per entity type
     *
     * @var array
     */
    protected $_ignoredAttributeCodes = array(
        'global' => array('entity_id', 'attribute_set_id', 'entity_type_id')
    );

    /**
     * Field name in session for saving store id
     *
     * @var string
     */
    protected $_storeIdSessionField = 'store_id';

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
     * Check if quote already exist with provided quoteId for creating
     *
     * @param int $quoteId
     * @return bool
     */
    protected function _isQuoteExist($quoteId)
    {
        if (empty($quoteId)) {
            return false;
        }

        try {
            $quote = $this->_getQuote($quoteId);
        } catch (Mage_Api_Exception $e) {
            return false;
        }

        if (!is_null($quote->getId())) {
            $this->_fault('quote_already_exist');
        }

        return false;
    }

    /**
     * Retrieves store id from store code, if no store id specified,
     * it use set session or admin store
     *
     * @param string|int $store
     * @return int
     */
    protected function _getStoreId($store = null)
    {
        if (is_null($store)) {
            $store = ($this->_getSession()->hasData($this->_storeIdSessionField)
                ? $this->_getSession()->getData($this->_storeIdSessionField) : 0);
        }

        try {
            $storeId = Mage::app()->getStore($store)->getId();

        } catch (Mage_Core_Model_Store_Exception $e) {
            $this->_fault('store_not_exists');
        }

        return $storeId;
    }

    /**
     * Retrieves quote by quote identifier and store code or by quote identifier
     *
     * @param int $quoteId
     * @param string|int $store
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote($quoteId, $store = null)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('Mage_Sales_Model_Quote');

        if (!(is_string($store) || is_integer($store))) {
            $quote->loadByIdWithoutStore($quoteId);
        } else {
            $storeId = $this->_getStoreId($store);

            $quote->setStoreId($storeId)
                ->load($quoteId);
        }
        if (is_null($quote->getId())) {
            $this->_fault('quote_not_exists');
        }

        return $quote;
    }

    /**
     * Get store identifier by quote identifier
     *
     * @param int $quoteId
     * @return int
     */
    protected function _getStoreIdFromQuote($quoteId)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getModel('Mage_Sales_Model_Quote')
            ->loadByIdWithoutStore($quoteId);

        return $quote->getStoreId();
    }

    /**
     * Update attributes for entity
     *
     * @param array $data
     * @param Mage_Core_Model_Abstract $object
     * @param string $type
     * @param array|null $attributes
     * @return Mage_Checkout_Model_Api_Resource
     */
    protected function _updateAttributes($data, $object, $type, array $attributes = null)
    {
        foreach ($data as $attribute => $value) {
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
     * @param string $type
     * @param array $attributes
     * @return Mage_Checkout_Model_Api_Resource
     */
    protected function _getAttributes($object, $type, array $attributes = null)
    {
        $result = array();
        if (!is_object($object)) {
            return $result;
        }
        foreach ($object->getData() as $attribute => $value) {
            if (is_object($value)) {
                continue;
            }

            if ($this->_apiHelper->isAttributeAllowed($attribute, $type, $this->_ignoredAttributeCodes, $attributes)) {
                $result[$attribute] = $value;
            }
        }
        if (isset($this->_attributesMap[$type])) {
            foreach ($this->_attributesMap[$type] as $alias => $attributeCode) {
                $result[$alias] = $object->getData($attributeCode);
            }
        }
        foreach ($this->_attributesMap['global'] as $alias => $attributeCode) {
            $result[$alias] = $object->getData($attributeCode);
        }
        return $result;
    }
}
