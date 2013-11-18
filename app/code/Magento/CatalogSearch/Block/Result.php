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
 * @category    Magento
 * @package     Magento_CatalogSearch
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Product search result block
 *
 * @category   Magento
 * @package    Magento_CatalogSearch
 * @module     Catalog
 */
namespace Magento\CatalogSearch\Block;

class Result extends \Magento\Core\Block\Template
{
    /**
     * Catalog Product collection
     *
     * @var \Magento\CatalogSearch\Model\Resource\Fulltext\Collection
     */
    protected $_productCollection;

    /**
     * Catalog search data
     *
     * @var \Magento\CatalogSearch\Helper\Data
     */
    protected $_catalogSearchData = null;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Catalog\Model\Layer $catalogLayer
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchData
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Catalog\Model\Layer $catalogLayer,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        array $data = array()
    ) {
        $this->_catalogLayer = $catalogLayer;
        $this->_storeManager = $storeManager;
        $this->_catalogSearchData = $catalogSearchData;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Retrieve query model object
     *
     * @return \Magento\CatalogSearch\Model\Query
     */
    protected function _getQuery()
    {
        return $this->helper('Magento\CatalogSearch\Helper\Data')->getQuery();
    }

    /**
     * Prepare layout
     *
     * @return \Magento\CatalogSearch\Block\Result
     */
    protected function _prepareLayout()
    {
        // add Home breadcrumb
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $title = __("Search results for: '%1'", $this->helper('Magento\CatalogSearch\Helper\Data')->getQueryText());

            $breadcrumbs->addCrumb('home', array(
                'label' => __('Home'),
                'title' => __('Go to Home Page'),
                'link'  => $this->_storeManager->getStore()->getBaseUrl(),
            ))->addCrumb('search', array(
                'label' => $title,
                'title' => $title
            ));
        }

        // modify page title
        $title = __("Search results for: '%1'", $this->helper('Magento\CatalogSearch\Helper\Data')->getEscapedQueryText());
        $this->getLayout()->getBlock('head')->setTitle($title);

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
     * @return \Magento\Catalog\Block\Product\ListProduct
     */
    public function getListBlock()
    {
        return $this->getChildBlock('search_result_list');
    }

    /**
     * Set search available list orders
     *
     * @return \Magento\CatalogSearch\Block\Result
     */
    public function setListOrders()
    {
        $category = $this->_catalogLayer->getCurrentCategory();
        /* @var $category \Magento\Catalog\Model\Category */
        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);
        $availableOrders = array_merge(array(
            'relevance' => __('Relevance')
        ), $availableOrders);

        $this->getListBlock()
            ->setAvailableOrders($availableOrders)
            ->setDefaultDirection('desc')
            ->setSortBy('relevance');

        return $this;
    }

    /**
     * Set available view mode
     *
     * @return \Magento\CatalogSearch\Block\Result
     */
    public function setListModes()
    {
        $this->getListBlock()
            ->setModes(array(
                'grid' => __('Grid'),
                'list' => __('List'))
            );
        return $this;
    }

    /**
     * Set Search Result collection
     *
     * @return \Magento\CatalogSearch\Block\Result
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
     * @return \Magento\CatalogSearch\Model\Resource\Fulltext\Collection
     */
    protected function _getProductCollection()
    {
        if (is_null($this->_productCollection)) {
            $this->_productCollection = $this->getListBlock()->getLoadedProductCollection();
        }

        return $this->_productCollection;
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
        if ($this->_catalogSearchData->isMinQueryLength()) {
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
        return $this->_catalogSearchData->getNoteMessages();
    }
}
