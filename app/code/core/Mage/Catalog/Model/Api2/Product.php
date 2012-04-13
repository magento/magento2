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
 * @package     Mage_Catalog
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Abstract Api2 model for product instance
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Api2_Product extends Mage_Api2_Model_Resource
{
    /**
     * Get available attributes of API resource
     *
     * @param string $userType
     * @param string $operation
     * @return array
     */
    public function getAvailableAttributes($userType, $operation)
    {
        $attributes = $this->getAvailableAttributesFromConfig();
        /** @var $entityType Mage_Eav_Model_Entity_Type */
        $entityType = Mage::getModel('Mage_Eav_Model_Entity_Type')->loadByCode('catalog_product');
        $entityOnlyAttrs = $this->getEntityOnlyAttributes($userType, $operation);
        /** @var $attribute Mage_Catalog_Model_Resource_Eav_Attribute */
        foreach ($entityType->getAttributeCollection() as $attribute) {
            if ($this->_isAttributeVisible($attribute, $userType)) {
                $attributes[$attribute->getAttributeCode()] = $attribute->getFrontendLabel();
            }
        }
        $excludedAttrs = $this->getExcludedAttributes($userType, $operation);
        $includedAttrs = $this->getIncludedAttributes($userType, $operation);
        foreach ($attributes as $code => $label) {
            if (in_array($code, $excludedAttrs) || ($includedAttrs && !in_array($code, $includedAttrs))) {
                unset($attributes[$code]);
            }
            if (in_array($code, $entityOnlyAttrs)) {
                $attributes[$code] .= ' *';
            }
        }

        return $attributes;
    }

    /**
     * Define if attribute should be visible for passed user type
     *
     * @param Mage_Catalog_Model_Resource_Eav_Attribute $attribute
     * @param string $userType
     * @return bool
     */
    protected function _isAttributeVisible(Mage_Catalog_Model_Resource_Eav_Attribute $attribute, $userType)
    {
        $isAttributeVisible = false;
        if ($userType == Mage_Api2_Model_Auth_User_Admin::USER_TYPE) {
            $isAttributeVisible = $attribute->getIsVisible();
        } else {
            $systemAttributesForNonAdmin = array(
                'sku', 'name', 'short_description', 'description', 'tier_price', 'meta_title', 'meta_description',
                'meta_keyword',
            );
            if ($attribute->getIsUserDefined()) {
                $isAttributeVisible = $attribute->getIsVisibleOnFront();
            } else if (in_array($attribute->getAttributeCode(), $systemAttributesForNonAdmin)) {
                $isAttributeVisible = true;
            }
        }
        return (bool)$isAttributeVisible;
    }
}
