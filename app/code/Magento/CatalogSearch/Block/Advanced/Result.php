<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block\Advanced;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Model\Advanced;
use Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Advanced search result
 *
 * @api
 */
class Result extends Template
{
    /**
     * Url factory
     *
     * @var UrlFactory
     */
    protected $_urlFactory;

    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * Catalog search advanced
     *
     * @var Advanced
     */
    protected $_catalogSearchAdvanced;

    /**
     * @param Context $context
     * @param Advanced $catalogSearchAdvanced
     * @param LayerResolver $layerResolver
     * @param UrlFactory $urlFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Advanced $catalogSearchAdvanced,
        LayerResolver $layerResolver,
        UrlFactory $urlFactory,
        array $data = []
    ) {
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_catalogLayer = $layerResolver->get();
        $this->_urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->getTitle()->set($this->getPageTitle());
        $breadcrumbs = $this->getLayout()->getBlock('breadcrumbs');
        if ($breadcrumbs) {
            $breadcrumbs->addCrumb(
                'home',
                [
                    'label' => __('Home'),
                    'title' => __('Go to Home Page'),
                    'link' => $this->_storeManager->getStore()->getBaseUrl()
                ]
            )->addCrumb(
                'search',
                ['label' => __('Catalog Advanced Search'), 'link' => $this->getUrl('*/*/')]
            )->addCrumb(
                'search_result',
                ['label' => __('Results')]
            );
        }
        return parent::_prepareLayout();
    }

    /**
     * Get page title
     *
     * @return \Magento\Framework\Phrase
     */
    private function getPageTitle()
    {
        return __('Advanced Search Results');
    }

    /**
     * Set order options
     *
     * @return void
     */
    public function setListOrders()
    {
        /* @var $category \Magento\Catalog\Model\Category */
        $category = $this->_catalogLayer->getCurrentCategory();

        $availableOrders = $category->getAvailableSortByOptions();
        unset($availableOrders['position']);

        $this->getChildBlock('search_result_list')->setAvailableOrders($availableOrders);
    }

    /**
     * Set view mode options
     *
     * @return void
     */
    public function setListModes()
    {
        $this->getChildBlock('search_result_list')->setModes(['grid' => __('Grid'), 'list' => __('List')]);
    }

    /**
     * @return void
     */
    public function setListCollection()
    {
        $this->getChildBlock('search_result_list')->setCollection($this->_getProductCollection());
    }

    /**
     * @return Collection
     */
    protected function _getProductCollection()
    {
        return $this->getSearchModel()->getProductCollection();
    }

    /**
     * @return Advanced
     */
    public function getSearchModel()
    {
        return $this->_catalogSearchAdvanced;
    }

    /**
     * @return mixed
     */
    public function getResultCount()
    {
        if (!$this->getData('result_count')) {
            $size = $this->getSearchModel()->getProductCollection()->getSize();
            $this->setResultCount($size);
        }
        return $this->getData('result_count');
    }

    /**
     * @return string
     */
    public function getProductListHtml()
    {
        return $this->getChildHtml('search_result_list');
    }

    /**
     * @return string
     */
    public function getFormUrl()
    {
        return $this->_urlFactory->create()->addQueryParams(
            $this->getRequest()->getQueryValue()
        )->getUrl(
            '*/*/',
            ['_escape' => true]
        );
    }

    /**
     * @return array
     */
    public function getSearchCriterias()
    {
        $searchCriterias = $this->getSearchModel()->getSearchCriterias();
        $middle = ceil(count($searchCriterias) / 2);
        $left = array_slice($searchCriterias, 0, $middle);
        $right = array_slice($searchCriterias, $middle);

        return ['left' => $left, 'right' => $right];
    }
}
