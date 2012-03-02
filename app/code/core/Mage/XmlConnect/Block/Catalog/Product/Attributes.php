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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product additional attributes xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Attributes extends Mage_Catalog_Block_Product_View_Attributes
{
    /**
     * Add additional information (attributes) to current product xml object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_XmlConnect_Model_Simplexml_Element $productXmlObject
     */
    public function addAdditionalData(
        Mage_Catalog_Model_Product $product, Mage_XmlConnect_Model_Simplexml_Element $productXmlObject
    ) {
        if ($product && $productXmlObject && $product->getId()) {
            $this->_product = $product;
            $additionalData = $this->getAdditionalData();
            if (!empty($additionalData)) {
                $attributesXmlObj = $productXmlObject->addChild('additional_attributes');
                foreach ($additionalData as $data) {
                    $attribute = Mage::helper('Mage_Catalog_Helper_Output')
                        ->productAttribute($product, $data['value'], $data['code']);
                    /** @var $attrXmlObject Mage_XmlConnect_Model_Simplexml_Element */
                    $attrXmlObject = $attributesXmlObj->addChild('item');
                    $attrXmlObject->addCustomChild('label', $data['label']);
                    $attrXmlObject->addCustomChild('value', $attribute);
                }
            }
        }
    }
}
