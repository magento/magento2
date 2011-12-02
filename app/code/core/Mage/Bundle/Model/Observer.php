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
 * @package     Mage_Bundle
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Bundle Products Observer
 *
 * @category    Mage
 * @package     Mage_Bundle
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Bundle_Model_Observer
{
    /**
     * Setting Bundle Items Data to product for father processing
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function prepareProductSave($observer)
    {
        $request = $observer->getEvent()->getRequest();
        $product = $observer->getEvent()->getProduct();

        if (($items = $request->getPost('bundle_options')) && !$product->getCompositeReadonly()) {
            $product->setBundleOptionsData($items);
        }

        if (($selections = $request->getPost('bundle_selections')) && !$product->getCompositeReadonly()) {
            $product->setBundleSelectionsData($selections);
        }

        if ($product->getPriceType() == '0' && !$product->getOptionsReadonly()) {
            $product->setCanSaveCustomOptions(true);
            if ($customOptions = $product->getProductOptions()) {
                foreach (array_keys($customOptions) as $key) {
                    $customOptions[$key]['is_delete'] = 1;
                }
                $product->setProductOptions($customOptions);
            }
        }

        $product->setCanSaveBundleSelections(
            (bool)$request->getPost('affect_bundle_product_selections') && !$product->getCompositeReadonly()
        );

        return $this;
    }

    /**
     * Append bundles in upsell list for current product
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function appendUpsellProducts($observer)
    {
        /* @var $product Mage_Catalog_Model_Product */
        $product = $observer->getEvent()->getProduct();

        /**
         * Check is current product type is allowed for bundle selection product type
         */
        if (!in_array($product->getTypeId(), Mage::helper('Mage_Bundle_Helper_Data')->getAllowedSelectionTypes())) {
            return $this;
        }

        /* @var $collection Mage_Catalog_Model_Resource_Product_Link_Product_Collection */
        $collection = $observer->getEvent()->getCollection();
        $limit      = $observer->getEvent()->getLimit();
        if (is_array($limit)) {
            if (isset($limit['upsell'])) {
                $limit = $limit['upsell'];
            } else {
                $limit = 0;
            }
        }

        /* @var $resource Mage_Bundle_Model_Resource_Selection */
        $resource   = Mage::getResourceSingleton('Mage_Bundle_Model_Resource_Selection');

        $productIds = array_keys($collection->getItems());
        if (!is_null($limit) && $limit <= count($productIds)) {
            return $this;
        }

        // retrieve bundle product ids
        $bundleIds  = $resource->getParentIdsByChild($product->getId());
        // exclude up-sell product ids
        $bundleIds  = array_diff($bundleIds, $productIds);

        if (!$bundleIds) {
            return $this;
        }

        /* @var $bundleCollection Mage_Catalog_Model_Resource_Product_Collection */
        $bundleCollection = $product->getCollection()
            ->addAttributeToSelect(Mage::getSingleton('Mage_Catalog_Model_Config')->getProductAttributes())
            ->addStoreFilter()
            ->addMinimalPrice()
            ->addFinalPrice()
            ->addTaxPercents()
            ->setVisibility(Mage::getSingleton('Mage_Catalog_Model_Product_Visibility')->getVisibleInCatalogIds());

        if (!is_null($limit)) {
            $bundleCollection->setPageSize($limit);
        }
        $bundleCollection->addFieldToFilter('entity_id', array('in' => $bundleIds))
            ->setFlag('do_not_use_category_id', true);

        if ($collection instanceof Varien_Data_Collection) {
            foreach ($bundleCollection as $item) {
                $collection->addItem($item);
            }
        } elseif ($collection instanceof Varien_Object) {
            $items = $collection->getItems();
            foreach ($bundleCollection as $item) {
                $items[$item->getEntityId()] = $item;
            }
            $collection->setItems($items);
        }

        return $this;
    }

    /**
     * Append selection attributes to selection's order item
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function appendBundleSelectionData($observer)
    {
        $orderItem = $observer->getEvent()->getOrderItem();
        $quoteItem = $observer->getEvent()->getItem();

        if ($attributes = $quoteItem->getProduct()->getCustomOption('bundle_selection_attributes')) {
            $productOptions = $orderItem->getProductOptions();
            $productOptions['bundle_selection_attributes'] = $attributes->getValue();
            $orderItem->setProductOptions($productOptions);
        }

        return $this;
    }

    /**
     * Add price index data for catalog product collection
     * only for front end
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function loadProductOptions($observer)
    {
        $collection = $observer->getEvent()->getCollection();
        /* @var $collection Mage_Catalog_Model_Resource_Product_Collection */
        $collection->addPriceData();

        return $this;
    }

    /**
     * duplicating bundle options and selections
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function duplicateProduct($observer)
    {
        $product = $observer->getEvent()->getCurrentProduct();

        if ($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            //do nothing if not bundle
            return $this;
        }

        $newProduct = $observer->getEvent()->getNewProduct();

        $product->getTypeInstance()->setStoreFilter($product->getStoreId(), $product);
        $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);
        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );
        $optionCollection->appendSelections($selectionCollection);

        $optionRawData = array();
        $selectionRawData = array();

        $i = 0;
        foreach ($optionCollection as $option) {
            $optionRawData[$i] = array(
                    'required' => $option->getData('required'),
                    'position' => $option->getData('position'),
                    'type' => $option->getData('type'),
                    'title' => $option->getData('title')?$option->getData('title'):$option->getData('default_title'),
                    'delete' => ''
                );
            foreach ($option->getSelections() as $selection) {
                $selectionRawData[$i][] = array(
                    'product_id' => $selection->getProductId(),
                    'position' => $selection->getPosition(),
                    'is_default' => $selection->getIsDefault(),
                    'selection_price_type' => $selection->getSelectionPriceType(),
                    'selection_price_value' => $selection->getSelectionPriceValue(),
                    'selection_qty' => $selection->getSelectionQty(),
                    'selection_can_change_qty' => $selection->getSelectionCanChangeQty(),
                    'delete' => ''
                );
            }
            $i++;
        }

        $newProduct->setBundleOptionsData($optionRawData);
        $newProduct->setBundleSelectionsData($selectionRawData);
        return $this;
    }

    /**
     * Setting attribute tab block for bundle
     *
     * @param Varien_Object $observer
     * @return Mage_Bundle_Model_Observer
     */
    public function setAttributeTabBlock($observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE) {
            Mage::helper('Mage_Adminhtml_Helper_Catalog')
                ->setAttributeTabBlock('Mage_Bundle_Block_Adminhtml_Catalog_Product_Edit_Tab_Attributes');
        }
        return $this;
    }
}
