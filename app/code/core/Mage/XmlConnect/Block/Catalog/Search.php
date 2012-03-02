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
 * Product search results renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Search extends Mage_XmlConnect_Block_Catalog
{
    /**
     * Search results xml renderer
     * XML also contains filters that can be apply (accorfingly already applyed filters
     * and search query) and sort fields
     *
     * @return string
     */
    protected function _toHtml()
    {
        $searchXmlObject  = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<search></search>');
        $filtersXmlObject = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element', '<filters></filters>');

        $helper = Mage::helper('Mage_CatalogSearch_Helper_Data');
        if (method_exists($helper, 'getEngine')) {
            $engine = Mage::helper('Mage_CatalogSearch_Helper_Data')->getEngine();
            if ($engine instanceof Varien_Object) {
                $isLayeredNavigationAllowed = $engine->isLayeredNavigationAllowed();
            } else {
                $isLayeredNavigationAllowed = true;
            }
        } else {
            $isLayeredNavigationAllowed = true;
        }

        $hasMoreProductItems = 0;

        /**
         * Products
         */
        $productListBlock = $this->getChild('product_list');
        if ($productListBlock) {
            $layer = Mage::getSingleton('Mage_CatalogSearch_Model_Layer');
            $productsXmlObj = $productListBlock->setLayer($layer)
                ->setNeedBlockApplyingFilters(!$isLayeredNavigationAllowed)->getProductsXmlObject();
            $searchXmlObject->appendChild($productsXmlObj);
            $hasMoreProductItems = (int)$productListBlock->getHasProductItems();
        }

        $searchXmlObject->addAttribute('has_more_items', $hasMoreProductItems);

        /**
         * Filters
         */
        $showFiltersAndOrders = (bool) count($productsXmlObj);
        $requestParams = $this->getRequest()->getParams();
        foreach ($requestParams as $key => $value) {
            if (0 === strpos($key, parent::REQUEST_SORT_ORDER_PARAM_REFIX)
                || 0 === strpos($key, parent::REQUEST_FILTER_PARAM_REFIX)
            ) {
                $showFiltersAndOrders = false;
                break;
            }
        }
        if ($isLayeredNavigationAllowed && $productListBlock && $showFiltersAndOrders) {
            $filters = $productListBlock->getCollectedFilters();
            /**
             * Render filters xml
             */
            foreach ($filters as $filter) {
                if (!$this->_isFilterItemsHasValues($filter)) {
                    continue;
                }
                $item = $filtersXmlObject->addChild('item');
                $item->addChild('name', $searchXmlObject->escapeXml($filter->getName()));
                $item->addChild('code', $filter->getRequestVar());
                $values = $item->addChild('values');

                foreach ($filter->getItems() as $valueItem) {
                    $count = (int)$valueItem->getCount();
                    if (!$count) {
                        continue;
                    }
                    $value = $values->addChild('value');
                    $value->addChild('id', $valueItem->getValueString());
                    $value->addChild('label', $searchXmlObject->escapeXml($valueItem->getLabel()));
                    $value->addChild('count', $count);
                }
            }
            $searchXmlObject->appendChild($filtersXmlObject);
        }

        /**
         * Sort fields
         */
        if ($showFiltersAndOrders) {
            $searchXmlObject->appendChild($this->getProductSortFeildsXmlObject());
        }

        return $searchXmlObject->asNiceXml();
    }

    /**
     * Check if items of specified filter have values
     *
     * @param object $filter filter model
     * @return bool
     */
    protected function _isFilterItemsHasValues($filter)
    {
        if (!$filter->getItemsCount()) {
            return false;
        }
        foreach ($filter->getItems() as $valueItem) {
            if ((int)$valueItem->getCount()) {
                return true;
            }
        }
        return false;
    }
}
