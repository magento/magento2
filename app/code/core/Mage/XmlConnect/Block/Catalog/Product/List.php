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
 * Product list xml renderer
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Block_Catalog_Product_List extends Mage_XmlConnect_Block_Catalog_Product
{
    /**
     * Store product collection
     *
     * @var Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected $_productCollection = null;

    /**
     * Store collected layered navigation filters whike applying them
     *
     * @var array
     */
    protected $_collectedFilters = array();

    /**
     * Produce products list xml object
     *
     * @return Mage_XmlConnect_Model_Simplexml_Element
     */
    public function getProductsXmlObject()
    {
        $productsXmlObj = Mage::getModel('Mage_XmlConnect_Model_Simplexml_Element',
            array('data' => '<products></products>'));
        $collection     = $this->_getProductCollection();

        if (!$collection) {
            return false;
        }
        foreach ($collection->getItems() as $product) {
            $productXmlObj = $this->productToXmlObject($product);
            if ($productXmlObj) {
                $productsXmlObj->appendChild($productXmlObj);
            }
        }

        return $productsXmlObj;
    }

    /**
     * Getter for collected layered navigation filters
     *
     * @return array
     */
    public function getCollectedFilters()
    {
        return $this->_collectedFilters;
    }

    /**
     * Retrieve product collection with all prepared data and limitations
     *
     * @return Mage_Eav_Model_Entity_Collection_Abstract
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $filters        = array();
            $request        = $this->getRequest();
            $requestParams  = $request->getParams();
            $layer          = $this->getLayer();
            if (!$layer) {
                return null;
            }
            $category       = $this->getCategory();
            if ($category && is_object($category) && $category->getId()) {
                $layer->setCurrentCategory($category);
            }
            if (!$this->getNeedBlockApplyingFilters()) {
                $attributes     = $layer->getFilterableAttributes();
                /**
                 * Apply filters
                 */
                foreach ($attributes as $attributeItem) {
                    $attributeCode  = $attributeItem->getAttributeCode();
                    list($filterModel, $filterBlock) = $this->helper('Mage_XmlConnect_Helper_Data')->getFilterByKey($attributeCode);

                    $filterModel->setLayer($layer)->setAttributeModel($attributeItem);

                    $filterParam = parent::REQUEST_FILTER_PARAM_REFIX . $attributeCode;
                    /**
                     * Set new request var
                     */
                    if (isset($requestParams[$filterParam])) {
                        $filterModel->setRequestVar($filterParam);
                    }
                    $filterModel->apply($request, $filterBlock);
                    $filters[] = $filterModel;
                }

                /**
                 * Separately apply and save category filter
                 */
                list($categoryFilter, $categoryFilterBlock) = $this->helper('Mage_XmlConnect_Helper_Data')->getFilterByKey('category');
                $filterParam = parent::REQUEST_FILTER_PARAM_REFIX . $categoryFilter->getRequestVar();
                $categoryFilter->setLayer($layer)->setRequestVar($filterParam)
                    ->apply($this->getRequest(), $categoryFilterBlock);
                $filters[] = $categoryFilter;

                $this->_collectedFilters = $filters;
            }

            /**
             * Products
             */
            $layer      = $this->getLayer();
            $collection = $layer->getProductCollection();

            /**
             * Add rating and review summary, image attribute, apply sort params
             */
            $this->_prepareCollection($collection);

            /**
             * Apply offset and count
             */
            $offset = (int)$request->getParam('offset', 0);
            $count  = (int)$request->getParam('count', 0);
            $count  = $count <= 0 ? 1 : $count;
            if ($offset + $count < $collection->getSize()) {
                $this->setHasProductItems(1);
            }
            $collection->getSelect()->limit($count, $offset);
            $collection->setFlag('require_stock_items', true);

            $this->_productCollection = $collection;
        }
        return $this->_productCollection;
    }

    /**
     * Add image attribute and apply sort fields to product collection
     *
     * @param Mage_Eav_Model_Entity_Collection_Abstract $collection
     * @return Mage_XmlConnect_Block_Catalog_Product_List
     */
    protected function _prepareCollection($collection)
    {
        /**
         * Apply sort params
         */
        $reguest = $this->getRequest();
        foreach ($reguest->getParams() as $key => $value) {
            if (0 === strpos($key, parent::REQUEST_SORT_ORDER_PARAM_REFIX)) {
                $key = str_replace(parent::REQUEST_SORT_ORDER_PARAM_REFIX, '', $key);
                if ($value != 'desc') {
                    $value = 'asc';
                }
                $collection->addAttributeToSort($key, $value);
            }
        }
        $collection->addAttributeToSelect(array('image', 'name', 'description'));

        return $this;
    }

    /**
     * Render products list xml
     *
     * @return string
     */
    protected function _toHtml()
    {
        return $this->getProductsXmlObject()->asNiceXml();
    }
}
