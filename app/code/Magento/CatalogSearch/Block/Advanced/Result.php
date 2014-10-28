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
namespace Magento\CatalogSearch\Block\Advanced;

use Magento\Catalog\Model\Layer\Search as Layer;
use Magento\CatalogSearch\Model\Advanced;
use Magento\CatalogSearch\Model\Resource\Advanced\Collection;
use Magento\Framework\UrlFactory;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Advanced search result
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
     * @var Layer
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
     * @param Layer $layer
     * @param UrlFactory $urlFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Advanced $catalogSearchAdvanced,
        Layer $layer,
        UrlFactory $urlFactory,
        array $data = array()
    ) {
        $this->_catalogSearchAdvanced = $catalogSearchAdvanced;
        $this->_catalogLayer = $layer;
        $this->_urlFactory = $urlFactory;
        parent::__construct($context, $data);
    }

    /**
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
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
                array('label' => __('Catalog Advanced Search'), 'link' => $this->getUrl('*/*/'))
            )->addCrumb(
                'search_result',
                array('label' => __('Results'))
            );
        }
        return parent::_prepareLayout();
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
        $this->getChildBlock('search_result_list')->setModes(array('grid' => __('Grid'), 'list' => __('List')));
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
            $this->getRequest()->getQuery()
        )->getUrl(
            '*/*/',
            array('_escape' => true)
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

        return array('left' => $left, 'right' => $right);
    }
}
