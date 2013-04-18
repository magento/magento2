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
 * Block representing set of columns in product grid
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author     Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mage_Catalog_Block_Product_Configurable_AssociatedSelector_Backend_Grid_ColumnSet
    extends Mage_Backend_Block_Widget_Grid_ColumnSet
{
    /**
     * Registry instance
     *
     * @var Mage_Core_Model_Registry
     */
    protected $_registryManager;

    /**
     * Product type configurable instance
     *
     * @var Mage_Catalog_Model_Product_Type_Configurable
     */
    protected $_productType;

    /**
     * @param Mage_Core_Block_Template_Context $context
     * @param Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory $generatorFactory
     * @param Mage_Core_Model_Registry $registryManager
     * @param Mage_Backend_Model_Widget_Grid_SubTotals $subtotals
     * @param Mage_Backend_Model_Widget_Grid_Totals $totals
     * @param Mage_Catalog_Model_Product_Type_Configurable $productType
     * @param array $data
     */
    public function __construct(
        Mage_Core_Block_Template_Context $context,
        Mage_Backend_Model_Widget_Grid_Row_UrlGeneratorFactory $generatorFactory,
        Mage_Core_Model_Registry $registryManager,
        Mage_Backend_Model_Widget_Grid_SubTotals $subtotals,
        Mage_Backend_Model_Widget_Grid_Totals $totals,
        Mage_Catalog_Model_Product_Type_Configurable $productType,
        array $data = array()
    ) {
        parent::__construct($context, $context->getHelperFactory()->get('Mage_Backend_Helper_Data'),
            $generatorFactory, $subtotals, $totals, $data
        );

        $this->_registryManager = $registryManager;
        $this->_productType = $productType;
    }

    /**
     * Retrieve currently edited product object
     *
     * @return Mage_Catalog_Model_Product
     */
    protected function _getProduct()
    {
        return $this->_registryManager->registry('current_product');
    }

    /**
     * Preparing layout
     *
     * @return Mage_Catalog_Block_Product_Configurable_AssociatedSelector_Backend_Grid_ColumnSet
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        $product = $this->_getProduct();
        $attributes = $this->_productType->getUsedProductAttributes($product);
        foreach ($attributes as $attribute) {
            /** @var $attribute Mage_Catalog_Model_Entity_Attribute */
            /** @var $block Mage_Backend_Block_Widget_Grid_Column */
            $block = $this->addChild(
                $attribute->getAttributeCode(),
                'Mage_Backend_Block_Widget_Grid_Column',
                array(
                    'header' => $attribute->getStoreLabel(),
                    'index' => $attribute->getAttributeCode(),
                    'type' => 'options',
                    'options' => $this->getOptions($attribute->getSource()),
                    'sortable' => false
                )
            );
            $block->setId($attribute->getAttributeCode())->setGrid($this);
        }
        return $this;
    }

    /**
     * Get option as hash
     *
     * @param Mage_Eav_Model_Entity_Attribute_Source_Abstract $sourceModel
     * @return array
     */
    private function getOptions(Mage_Eav_Model_Entity_Attribute_Source_Abstract $sourceModel)
    {
        $result = array();
        foreach ($sourceModel->getAllOptions() as $option) {
            if ($option['value'] != '') {
                $result[$option['value']] = $option['label'];
            }
        }
        return $result;
    }
}
