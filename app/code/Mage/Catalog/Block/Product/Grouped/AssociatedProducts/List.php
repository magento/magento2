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
class Mage_Catalog_Block_Product_Grouped_AssociatedProducts_List extends Mage_Backend_Block_Template
{
    /**
     * Store manager
     *
     * @var Mage_Core_Model_StoreManager
     */
    protected $_storeManager;

    /**
     * Registry
     *
     * @var Mage_Core_Model_Registry
     */
    protected $_registry;

    /**
     * @inheritdoc
     *
     * @param Mage_Backend_Block_Template_Context $context
     * @param Mage_Core_Model_Registry $storeManager
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_StoreManager $storeManager,
        Mage_Core_Model_Registry $registry,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_storeManager = $storeManager;
        $this->_registry = $registry;
    }

    /**
     * Retrieve grouped products
     *
     * @return array
     */
    public function getAssociatedProducts()
    {
        /** @var $product Mage_Catalog_Model_Product */
        $product = $this->_registry->registry('current_product');
        $associatedProducts = $product->getTypeInstance()
            ->getAssociatedProducts($product);
        $products = array();

        foreach ($associatedProducts as $product) {
            $products[] = array(
                'id'        => $product->getId(),
                'sku'       => $product->getSku(),
                'name'      => $product->getName(),
                'price'     => $this->_storeManager->getStore()->formatPrice($product->getPrice(), false),
                'qty'       => $product->getQty(),
                'position'  => $product->getPosition(),
            );
        }
        return $products;
    }
}
