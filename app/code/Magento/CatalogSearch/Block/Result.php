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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\CatalogSearch\Block;

use Magento\Catalog\Block\Product\ListProduct;
use Magento\Catalog\Model\Layer\Search as ModelLayer;
use Magento\CatalogSearch\Helper\Data;
use Magento\CatalogSearch\Model\Query;
use Magento\CatalogSearch\Model\Resource\Fulltext\Collection;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Product search result block
 */
class Result extends Template
{
    /**
     * Catalog Product collection
     *
     * @var Collection
     */
    protected $productCollection;

    /**
     * Catalog search data
     *
     * @var Data
     */
    protected $catalogSearchData;

    /**
     * Catalog layer
     *
     * @var ModelLayer
     */
    protected $catalogLayer;

    /**
     * @param Context $context
     * @param ModelLayer $catalogLayer
     * @param Data $catalogSearchData
     * @param array $data
     */
    public function __construct(
        Context $context,
        ModelLayer $catalogLayer,
        Data $catalogSearchData,
        array $data = array()
    ) {
        $this->catalogLayer = $catalogLayer;
        $this->catalogSearchData = $catalogSearchData;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve query model object
     *
     * @return Query
     */
    protected function _getQuery()
    {
        return $this->catalogSearchData->getQuery();
    }

    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $title = $this->getSearchQueryText();
        $this->pageConfig->setTitle($title);
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                array(
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                )
            )->addCrumb(
                'search',
                array('label' => $title, 'title' => $title)
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * Retrieve additional blocks html
     *
     * @return string
     */
    public function getAdditionalHtml()
    {
        return $this->getLayout()->getBlock('search_result_list')->getChildHtml('additional');
    }

    /**
     * Retrieve search list toolbar block
     *
     * @return ListProduct
     */
    public function getListBlock()
    {
        return $this->getChildBlock('search_result_list');
    }

    /**
     * Set search available list orders
     *
     * @return $this
     */
    public function setListOrders()
    {
        $category = $this->catalogLayer->getCurrentCategory();
        /* @var $category \Magento\Catalog\Model\Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders = array_merge(array('relevance' => __('Relevance')), $availableOrders);

        $this->getListBlock()->setAvailableOrders(
            $availableOrders
        )->setDefaultDirection(
            'desc'
        )->setSortBy(
            'relevance'
        );

        return $this;
    }

    /**
     * Set available view mode
     *
     * @return $this
     */
    public function setListModes()
    {
        $test = $this->getListBlock();
        $test->setModes(array('grid' => __('Grid'), 'list' => __('List')));
        return $this;
    }

    /**
     * Set Search Result collection
     *
     * @return $this
     */
    public function setListCollection()
    {
        //        $this->getListBlock()
        //           ->setCollection($this->_getProductCollection());
        return $this;
    }

    /**
     * Retrieve Search result list HTML output
     *
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }

    /**
     * Retrieve loaded category collection
     *
     * @return Collection
     */
    protected function _getProductCollection()
    {
        if (null === $this->productCollection) {
            $this->productCollection = $this->getListBlock()->getLoadedProductCollection();
        }

        return $this->productCollection;
    }

    /**
     * Get search query text
     *
     * @return string
     */
    public function getSearchQueryText()
    {
        return __("Search results for: '%1'", $this->catalogSearchData->getEscapedQueryText());
    }

    /**
     * Retrieve search result count
     *
     * @return string
     */
    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->_getProductCollection()->getSize();
            $this->_getQuery()->setNumResults($size);
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }

    /**
     * Retrieve No Result or Minimum query length Text
     *
     * @return string
     */
    public function getNoResultText()
    {
        if ($this->catalogSearchData->isMinQueryLength()) {
            return __('Minimum Search query length is %1', $this->_getQuery()->getMinQueryLength());
        }
        return $this->_getData('no_result_text');
    }

    /**
     * Retrieve Note messages
     *
     * @return array
     */
    public function getNoteMessages()
    {
        return $this->catalogSearchData->getNoteMessages();
    }
}
