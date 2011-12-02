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
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Grouped product type implementation
 *
 * @category   Mage
 * @package    Mage_Catalog
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Catalog_Model_Product_Type_Grouped extends Mage_Catalog_Model_Product_Type_Abstract
{
    const TYPE_CODE = 'grouped';

    /**
     * Cache key for Associated Products
     *
     * @var string
     */
    protected $_keyAssociatedProducts   = '_cache_instance_associated_products';

    /**
     * Cache key for Associated Product Ids
     *
     * @var string
     */
    protected $_keyAssociatedProductIds = '_cache_instance_associated_product_ids';

    /**
     * Cache key for Status Filters
     *
     * @var string
     */
    protected $_keyStatusFilters        = '_cache_instance_status_filters';

    /**
     * Product is composite properties
     *
     * @var bool
     */
    protected $_isComposite             = true;

    /**
     * Product is configurable
     *
     * @var bool
     */
    protected $_canConfigure            = true;

    /**
     * Return relation info about used products
     *
     * @return Varien_Object Object with information data
     */
    public function getRelationInfo()
    {
        $info = new Varien_Object();
        $info->setTable('catalog_product_link')
            ->setParentFieldName('product_id')
            ->setChildFieldName('linked_product_id')
            ->setWhere('link_type_id=' . Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);
        return $info;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     */
    public function getChildrenIds($parentId, $required = true)
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Link')
            ->getChildrenIds($parentId,
                Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return array
     */
    public function getParentIdsByChild($childId)
    {
        return Mage::getResourceSingleton('Mage_Catalog_Model_Resource_Product_Link')
            ->getParentIdsByChild($childId,
                Mage_Catalog_Model_Product_Link::LINK_TYPE_GROUPED);
    }

    /**
     * Retrieve array of associated products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getAssociatedProducts($product)
    {
        if (!$product->hasData($this->_keyAssociatedProducts)) {
            $associatedProducts = array();

            if (!Mage::app()->getStore()->isAdmin()) {
                $this->setSaleableStatus($product);
            }

            $collection = $this->getAssociatedProductCollection($product)
                ->addAttributeToSelect('*')
                ->addFilterByRequiredOptions()
                ->setPositionOrder()
                ->addStoreFilter($this->getStoreFilter($product))
                ->addAttributeToFilter('status', array('in' => $this->getStatusFilters($product)));

            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $product->setData($this->_keyAssociatedProducts, $associatedProducts);
        }
        return $product->getData($this->_keyAssociatedProducts);
    }

    /**
     * Add status filter to collection
     *
     * @param  int $status
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Grouped
     */
    public function addStatusFilter($status, $product)
    {
        $statusFilters = $product->getData($this->_keyStatusFilters);
        if (!is_array($statusFilters)) {
            $statusFilters = array();
        }

        $statusFilters[] = $status;
        $product->setData($this->_keyStatusFilters, $statusFilters);

        return $this;
    }

    /**
     * Set only saleable filter
     *
     * @param  Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Grouped
     */
    public function setSaleableStatus($product)
    {
        $product->setData($this->_keyStatusFilters,
            Mage::getSingleton('Mage_Catalog_Model_Product_Status')->getSaleableStatusIds());
        return $this;
    }

    /**
     * Return all assigned status filters
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getStatusFilters($product)
    {
        if (!$product->hasData($this->_keyStatusFilters)) {
            return array(
                Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
                Mage_Catalog_Model_Product_Status::STATUS_DISABLED
            );
        }
        return $product->getData($this->_keyStatusFilters);
    }

    /**
     * Retrieve related products identifiers
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getAssociatedProductIds($product)
    {
        if (!$product->hasData($this->_keyAssociatedProductIds)) {
            $associatedProductIds = array();
            foreach ($this->getAssociatedProducts($product) as $item) {
                $associatedProductIds[] = $item->getId();
            }
            $product->setData($this->_keyAssociatedProductIds, $associatedProductIds);
        }
        return $product->getData($this->_keyAssociatedProductIds);
    }

    /**
     * Retrieve collection of associated products
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Resource_Product_Link_Product_Collection
     */
    public function getAssociatedProductCollection($product)
    {
        $collection = $product->getLinkInstance()->useGroupedLinks()
            ->getProductCollection()
            ->setFlag('require_stock_items', true)
            ->setFlag('product_children', true)
            ->setIsStrongMode();
        $collection->setProduct($product);
        return $collection;
    }

    /**
     * Check is product available for sale
     *
     * @param Mage_Catalog_Model_Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        $salable = parent::isSalable($product);
        if (!is_null($salable)) {
            return $salable;
        }

        $salable = false;
        foreach ($this->getAssociatedProducts($product) as $associatedProduct) {
            $salable = $salable || $associatedProduct->isSalable();
        }
        return $salable;
    }

    /**
     * Save type related data
     *
     * @param Mage_Catalog_Model_Product $product
     * @return Mage_Catalog_Model_Product_Type_Grouped
     */
    public function save($product)
    {
        parent::save($product);
        $product->getLinkInstance()->saveGroupedLinks($product);
        return $this;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param Varien_Object $buyRequest
     * @param Mage_Catalog_Model_Product $product
     * @param string $processMode
     * @return array|string
     */
    protected function _prepareProduct(Varien_Object $buyRequest, $product, $processMode)
    {
        $productsInfo = $buyRequest->getSuperGroup();
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if (!$isStrictProcessMode || (!empty($productsInfo) && is_array($productsInfo))) {
            $products = array();
            $associatedProductsInfo = array();
            $associatedProducts = $this->getAssociatedProducts($product);
            if ($associatedProducts || !$isStrictProcessMode) {
                foreach ($associatedProducts as $subProduct) {
                    $subProductId = $subProduct->getId();
                    if(isset($productsInfo[$subProductId])) {
                        $qty = $productsInfo[$subProductId];
                        if (!empty($qty) && is_numeric($qty)) {

                            $_result = $subProduct->getTypeInstance()
                                ->_prepareProduct($buyRequest, $subProduct, $processMode);
                            if (is_string($_result) && !is_array($_result)) {
                                return $_result;
                            }

                            if (!isset($_result[0])) {
                                return Mage::helper('Mage_Checkout_Helper_Data')->__('Cannot process the item.');
                            }

                            if ($isStrictProcessMode) {
                                $_result[0]->setCartQty($qty);
                                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);
                                $_result[0]->addCustomOption('info_buyRequest',
                                    serialize(array(
                                        'super_product_config' => array(
                                            'product_type'  => self::TYPE_CODE,
                                            'product_id'    => $product->getId()
                                        )
                                    ))
                                );
                                $products[] = $_result[0];
                            } else {
                                $associatedProductsInfo[] = array($subProductId => $qty);
                                $product->addCustomOption('associated_product_' . $subProductId, $qty);
                            }
                        }
                    }
                }
            }

            if (!$isStrictProcessMode || count($associatedProductsInfo)) {
                $product->addCustomOption('product_type', self::TYPE_CODE, $product);
                $product->addCustomOption('info_buyRequest', serialize($buyRequest->getData()));

                $products[] = $product;
            }

            if (count($products)) {
                return $products;
            }
        }

        return Mage::helper('Mage_Catalog_Helper_Data')->__('Please specify the quantity of product(s).');
    }

    /**
     * Retrieve products divided into groups required to purchase
     * At least one product in each group has to be purchased
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array
     */
    public function getProductsToPurchaseByReqGroups($product)
    {
        return array($this->getAssociatedProducts($product));
    }

    /**
     * Prepare selected qty for grouped product's options
     *
     * @param  Mage_Catalog_Model_Product $product
     * @param  Varien_Object $buyRequest
     * @return array
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $superGroup = $buyRequest->getSuperGroup();
        $superGroup = (is_array($superGroup)) ? array_filter($superGroup, 'intval') : array();

        $options = array('super_group' => $superGroup);

        return $options;
    }
}
