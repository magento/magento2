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
 * Shopping cart configurable product xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Cart_Item_Renderer_Configurable extends Mage_XmlConnect_Block_Cart_Item_Renderer
{
    const CONFIGURABLE_PRODUCT_IMAGE = 'checkout/cart/configurable_product_image';
    const USE_PARENT_IMAGE           = 'parent';

    /**
     * Get item configurable product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getConfigurableProduct()
    {
        $option = $this->getItem()->getOptionByCode('product_type');
        if ($option) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get item configurable child product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getChildProduct()
    {
        $option = $this->getItem()->getOptionByCode('simple_product');
        if ($option) {
            return $option->getProduct();
        }
        return $this->getProduct();
    }

    /**
     * Get item product name
     *
     * @return string
     */
    public function getProductName()
    {
        return $this->getProduct()->getName();
    }

    /**
     * Get list of all options for product
     *
     * @return array
     */
    public function getOptionList()
    {
        /* @var $helper Mage_Catalog_Helper_Product_Configuration */
        $helper = Mage::helper('Mage_Catalog_Helper_Product_Configuration');
        return $helper->getConfigurableOptions($this->getItem());
    }
}
