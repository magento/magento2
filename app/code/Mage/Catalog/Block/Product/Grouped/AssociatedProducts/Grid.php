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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Products in grouped grid
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Block_Product_Grouped_AssociatedProducts_Grid extends Mage_Backend_Block_Widget_Grid
{
    /**
     * Input name product data will be serialized into
     */
    protected $_hiddenInputName;

    /**
     * Names of the inputs to serialize
     */
    protected $_fieldsToSave = array();

    protected function _construct()
    {
        parent::_construct();
        $this->setId('super_product_grid');
        $this->setDefaultSort('entity_id');
        $this->setSkipGenerateContent(true);
        $this->setUseAjax(true);
    }

    /**
     * Retrieve grouped products
     *
     * @return array
     */
    public function getAssociatedProducts()
    {
        $associatedProducts = Mage::registry('current_product')->getTypeInstance()
            ->getAssociatedProducts(Mage::registry('current_product'));
        $products = array();
        foreach ($associatedProducts as $product) {
            $products[$product->getId()] = array(
                'qty'       => $product->getQty(),
                'position'  => $product->getPosition()
            );
        }
        return $this->helper('Mage_Core_Helper_Data')->jsonEncode($products);
    }

    /**
     * Get associated product ids
     *
     * @return array
     */
    public function getAssociatedProductIds()
    {
        $associatedProducts = Mage::registry('current_product')->getTypeInstance()
            ->getAssociatedProducts(Mage::registry('current_product'));
        $ids = array();
        foreach ($associatedProducts as $product) {
            $ids[] = $product->getId();
        }
        return $this->helper('Mage_Core_Helper_Data')->jsonEncode($ids);
    }

    /**
     * Get hidden input name
     *
     * @return string
     */
    public function getHiddenInputName()
    {
        return $this->_hiddenInputName;
    }

    /**
     * Get fields names
     *
     * @return array
     */
    public function getFieldsToSave()
    {
        return $this->_fieldsToSave;
    }

    /**
     * Init function
     *
     * @param string $hiddenInputName
     * @param array $fieldsToSave
     */
    public function setGridData($hiddenInputName, $fieldsToSave = array())
    {
        $this->_hiddenInputName = $hiddenInputName;
        $this->_fieldsToSave = $fieldsToSave;
    }

    /**
     * Callback for jQuery UI sortable update
     *
     * @return string
     */
    public function getSortableUpdateCallback()
    {
        return <<<SCRIPT
function () {
    if(jQuery && jQuery('#grouped-product-container').data('groupedProduct')) {
        jQuery('#grouped-product-container').groupedProduct('updateRowsPositions');
    }
}
SCRIPT;
    }
}
