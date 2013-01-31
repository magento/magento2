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
 * @package     Mage_GoogleShopping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Google Content Item Types Model
 *
 * @category   Mage
 * @package    Mage_GoogleShopping
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_GoogleShopping_Model_Type extends Mage_Core_Model_Abstract
{
    /**
     * Mapping attributes collection
     *
     * @var Mage_GoogleShopping_Model_Resource_Attribute_Collection
     */
    protected $_attributesCollection;

    protected function _construct()
    {
        $this->_init('Mage_GoogleShopping_Model_Resource_Type');
    }

    /**
     * Load type model by Attribute Set Id and Target Country
     *
     * @param int $attributeSetId Attribute Set
     * @param string $targetCountry Two-letters country ISO code
     * @return Mage_GoogleShopping_Model_Type
     */
    public function loadByAttributeSetId($attributeSetId, $targetCountry)
    {
        return $this->getResource()
            ->loadByAttributeSetIdAndTargetCountry($this, $attributeSetId, $targetCountry);
    }

    /**
     * Prepare Entry data and attributes before saving in Google Content
     *
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @return Varien_Gdata_Gshopping_Entry
     */
    public function convertProductToEntry($product, $entry)
    {
        $map = $this->_getAttributesMapByProduct($product);
        $base = $this->_getBaseAttributes();
        $attributes = array_merge($base, $map);

        $this->_removeNonexistentAttributes($entry, array_keys($attributes));

        foreach ($attributes as $name => $attribute) {
            $attribute->convertAttribute($product, $entry);
        }

        return $entry;
    }

    /**
     * Return Product attribute values array
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array Product attribute values
     */
    protected function _getAttributesMapByProduct(Mage_Catalog_Model_Product $product)
    {
        $result = array();
        $group = Mage::getSingleton('Mage_GoogleShopping_Model_Config')->getAttributeGroupsFlat();
        foreach ($this->_getAttributesCollection() as $attribute) {
            $productAttribute = Mage::helper('Mage_GoogleShopping_Helper_Product')
                ->getProductAttribute($product, $attribute->getAttributeId());

            if ($productAttribute !== null) {
                // define final attribute name
                if ($attribute->getGcontentAttribute()) {
                    $name = $attribute->getGcontentAttribute();
                } else {
                    $name = Mage::helper('Mage_GoogleShopping_Helper_Product')->getAttributeLabel($productAttribute, $product->getStoreId());
                }

                if ($name !== null) {
                    $name = Mage::helper('Mage_GoogleShopping_Helper_Data')->normalizeName($name);
                    if (isset($group[$name])) {
                        // if attribute is in the group
                        if (!isset($result[$group[$name]])) {
                            $result[$group[$name]] = $this->_createAttribute($group[$name]);
                        }
                        // add group attribute to parent attribute
                        $result[$group[$name]]->addData(array(
                            'group_attribute_' . $name => $this->_createAttribute($name)->addData($attribute->getData())
                        ));
                        unset($group[$name]);
                    } else {
                        if (!isset($result[$name])) {
                            $result[$name] = $this->_createAttribute($name);
                        }
                        $result[$name]->addData($attribute->getData());
                    }
                }
            }
        }

        return $this->_initGroupAttributes($result);
    }

    /**
     * Retrun array with base attributes
     *
     * @return array
     */
    protected function _getBaseAttributes()
    {
        $names = Mage::getSingleton('Mage_GoogleShopping_Model_Config')->getBaseAttributes();
        $attributes = array();
        foreach ($names as $name) {
            $attributes[$name] = $this->_createAttribute($name);
        }

        return $this->_initGroupAttributes($attributes);
    }

    /**
     * Append to attributes array subattribute's models
     *
     * @param array $attributes
     * @return array
     */
    protected function _initGroupAttributes($attributes)
    {
        $group = Mage::getSingleton('Mage_GoogleShopping_Model_Config')->getAttributeGroupsFlat();
        foreach ($group as $child => $parent) {
            if (isset($attributes[$parent]) &&
                !isset($attributes[$parent]['group_attribute_' . $child])) {
                    $attributes[$parent]->addData(
                        array('group_attribute_' . $child => $this->_createAttribute($child))
                    );
            }
        }

        return $attributes;
    }

    /**
     * Prepare Google Content attribute model name
     *
     * @param string Attribute name
     * @return string Normalized attribute name
     */
    protected function _prepareModelName($string)
    {
        return uc_words(Mage::helper('Mage_GoogleShopping_Helper_Data')->normalizeName($string));
    }

    /**
     * Create attribute instance using attribute's name
     *
     * @param string $name
     * @return Mage_GoogleShopping_Model_Attribute
     */
    protected function _createAttribute($name)
    {
        $modelName = 'Mage_GoogleShopping_Model_Attribute_' . $this->_prepareModelName($name);
        $useDefault = false;
        try {
            $attributeModel = Mage::getModel($modelName);
            $useDefault = !$attributeModel;
        } catch (Exception $e) {
            $useDefault = true;
        }
        if ($useDefault) {
            $attributeModel = Mage::getModel('Mage_GoogleShopping_Model_Attribute_Default');
        }
        $attributeModel->setName($name);

        return $attributeModel;
    }

    /**
     * Retrieve type's attributes collection
     * It is protected, because only Type knows about its attributes
     *
     * @return Mage_GoogleShopping_Model_Resource_Attribute_Collection
     */
    protected function _getAttributesCollection()
    {
        if ($this->_attributesCollection === null) {
            $this->_attributesCollection = Mage::getResourceModel(
                    'Mage_GoogleShopping_Model_Resource_Attribute_Collection'
                )
                ->addAttributeSetFilter($this->getAttributeSetId(), $this->getTargetCountry());
        }
        return $this->_attributesCollection;
    }

    /**
     * Remove attributes which were removed from mapping.
     *
     * @param Varien_Gdata_Gshopping_Entry $entry
     * @param array $existAttributes
     * @return Varien_Gdata_Gshopping_Entry
     */
    protected function _removeNonexistentAttributes($entry, $existAttributes)
    {
        // attributes which can't be removed
        $ignoredAttributes = array(
            "id",
            "image_link",
            "content_language",
            "target_country",
            "expiration_date",
            "adult"
        );

        $contentAttributes = $entry->getContentAttributes();
        foreach ($contentAttributes as $contentAttribute) {
            $name = Mage::helper('Mage_GoogleShopping_Helper_Data')->normalizeName($contentAttribute->getName());
            if (!in_array($name, $ignoredAttributes) &&
                !in_array($existAttributes, $existAttributes)) {
                    $entry->removeContentAttribute($name);
            }
        }

        return $entry;
    }
}
