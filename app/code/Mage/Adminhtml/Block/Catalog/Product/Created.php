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
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Product after creation popup window
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Adminhtml_Block_Catalog_Product_Created extends Mage_Adminhtml_Block_Widget
{
    protected $_configurableProduct;
    protected $_product;

    /**
     * @var string
     */
    protected $_template = 'catalog/product/created.phtml';

    protected function _prepareLayout()
    {
        $this->addChild('close_button', 'Mage_Adminhtml_Block_Widget_Button', array(
            'label'   => Mage::helper('Mage_Catalog_Helper_Data')->__('Close Window'),
            'onclick' => 'addProduct(true)'
        ));
    }


    public function getCloseButtonHtml()
    {
        return $this->getChildHtml('close_button');
    }

    public function getProductId()
    {
        return (int) $this->getRequest()->getParam('id');
    }

    /**
     * Identifies edit mode of popup
     *
     * @return boolean
     */
    public function isEdit()
    {
        return (bool)$this->getRequest()->getParam('edit');
    }

    /**
     * Retrieve serialized json with configurable attributes values of simple
     *
     * @return string
     */
    public function getAttributesJson()
    {
        $result = array();
        foreach ($this->getAttributes() as $attribute) {
            $value = $this->getProduct()->getAttributeText($attribute->getAttributeCode());

            $result[] = array(
                'label'         => $value,
                'value_index'   => $this->getProduct()->getData($attribute->getAttributeCode()),
                'attribute_id'  => $attribute->getId()
            );
        }

        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($result);
    }

    /**
     * Retrieve array of attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        $configurableProduct = $this->getConfigurableProduct();
        if ($configurableProduct->getId()) {
            return $configurableProduct
                ->getTypeInstance()
                ->getUsedProductAttributes($configurableProduct);
        }

        $attributes = array();
        $attributesIds = $this->getRequest()->getParam('required');
        if ($attributesIds) {
            $product = $this->getProduct();
            $typeInstance = $product->getTypeInstance();
            foreach (explode(',', $attributesIds) as $attributeId) {
                $attribute = $typeInstance->getAttributeById($attributeId, $product);
                if ($attribute) {
                    $attributes[] = $attribute;
                }
            }
        }
        return $attributes;
    }

    /**
     * Retrieve configurable product for created/edited simple
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getConfigurableProduct()
    {
        if ($this->_configurableProduct === null) {
            $this->_configurableProduct = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStore(0)
                ->load($this->getRequest()->getParam('product'));
        }
        return $this->_configurableProduct;
    }

    /**
     * Retrieve product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if ($this->_product === null) {
            $this->_product = Mage::getModel('Mage_Catalog_Model_Product')
                ->setStore(0)
                ->load($this->getRequest()->getParam('id'));
        }
        return $this->_product;
    }
}
