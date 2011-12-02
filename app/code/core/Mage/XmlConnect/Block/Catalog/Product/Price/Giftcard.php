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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Giftcard product price xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_Price_Giftcard extends Mage_Bundle_Block_Catalog_Product_Price
{
    /**
     * Return minimal amount for Giftcard product using price model
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getMinAmount($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $product->getPriceModel()->getMinAmount($product);
    }

    /**
     * Return maximal amount for Giftcard product using price model
     *
     * @param Mage_Catalog_Model_Product $product
     * @return float
     */
    public function getMaxAmount($product = null)
    {
        if (is_null($product)) {
            $product = $this->getProduct();
        }
        return $product->getPriceModel()->getMaxAmount($product);
    }

    /**
     * Collect product prices to specified item xml object
     *
     * @param Mage_Catalog_Model_Product $product
     * @param Mage_XmlConnect_Model_Simplexml_Element $item
     */
    public function collectProductPrices(
        Mage_Catalog_Model_Product $product,
        Mage_XmlConnect_Model_Simplexml_Element $item
    ) {
        $this->setProduct($product);

        if ($product->getCanShowPrice() !== false) {
            $priceXmlObj = $item->addChild('price');

            if (($_min = $this->getMinAmount()) && ($_max = $this->getMaxAmount()) && ($_min == $_max)) {
                $priceXmlObj->addAttribute('regular', Mage::helper('Mage_Core_Helper_Data')->currency($_min, true, false));
            } elseif (($_min = $this->getMinAmount()) && $_min != 0) {
                $priceXmlObj->addAttribute(
                    'regular',
                    Mage::helper('Enterprise_GiftCard_Helper_Data')->__('From') . ': '
                        . Mage::helper('Mage_Core_Helper_Data')->currency($_min, true, false)
                );
            }
        }
    }
}
