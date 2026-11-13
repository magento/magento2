<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */

namespace Magento\CatalogSearch\Controller\Result;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Layer\SearchFactory as SearchLayerFactory;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\PopularSearchTerms;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Product\ProductList\Toolbar;

/**
 * Search result.
 */
class Index extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * No results default handle.
     */
    public const DEFAULT_NO_RESULT_HANDLE = 'catalogsearch_result_index_noresults';

    /**
     * Catalog session for storing catalog-related data
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    /**
     * @var SearchLayerFactory
     */
    private $searchLayerFactory;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param SearchLayerFactory|null $searchLayerFactory
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver,
        ?ToolbarMemorizer $toolbarMemorizer = null,
        ?SearchLayerFactory $searchLayerFactory = null
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
        $this->toolbarMemorizer = $toolbarMemorizer ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(ToolbarMemorizer::class);
        $this->searchLayerFactory = $searchLayerFactory ?:
            \Magento\Framework\App\ObjectManager::getInstance()->get(SearchLayerFactory::class);
    }

    /**
     * Display search result
     *
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);

        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $storeId = $this->_storeManager->getStore()->getId();
        $query->setStoreId($storeId);

        $queryText = $query->getQueryText();

        if (empty($queryText)) {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
            return;
        }

        // Negative ?p= value is not supported, redirect to a base version of category page.
        if ($this->_request->getParam(Toolbar::PAGE_PARM_NAME) < 0) {
            $this->getResponse()->setRedirect(
                $this->_url->getUrl('*/*', ['_current' => true, '_query' => [Toolbar::PAGE_PARM_NAME => null]])
            );
            return;
        }

        $catalogSearchHelper = $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class);

        $getAdditionalRequestParameters = $this->getRequest()->getParams();
        unset($getAdditionalRequestParameters[QueryFactory::QUERY_VAR_NAME]);

        if ($this->shouldRedirectOnToolbarAction()) {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
            return;
        }

        if (empty($getAdditionalRequestParameters) &&
            $this->_objectManager->get(PopularSearchTerms::class)->isCacheable($queryText, $storeId)
        ) {
            $this->getCacheableResult($catalogSearchHelper, $query);
        } else {
            $this->getNotCacheableResult($catalogSearchHelper, $query);
        }
    }

    /**
     * Get layout handles, adding the no results handle when it is defined and the search term has no results
     *
     * @param \Magento\Search\Model\Query $query
     * @return array|null
     */
    private function getLayoutHandles($query)
    {
        if (!$this->isNoResultsHandleDefined() || !$this->hasNoResults($query)) {
            return null;
        }

        $this->_view->getPage()->initLayout();
        $handles = $this->_view->getLayout()->getUpdate()->getHandles();
        $handles[] = static::DEFAULT_NO_RESULT_HANDLE;

        return $handles;
    }

    /**
     * Check whether any layout file of the current area and theme defines the no results handle
     *
     * @return bool
     */
    private function isNoResultsHandleDefined()
    {
        $layoutUpdates = $this->_view->getLayout()->getUpdate()->getFileLayoutUpdatesXml();
        $handleXpath = sprintf("*[self::handle or self::layout][@id='%s']", static::DEFAULT_NO_RESULT_HANDLE);

        return $layoutUpdates->xpath($handleXpath) !== [];
    }

    /**
     * Check whether the search term has no results, ignoring layered navigation filters
     *
     * @param \Magento\Search\Model\Query $query
     * @return bool
     */
    private function hasNoResults($query)
    {
        if ((int)$query->getNumResults() > 0) {
            return false;
        }

        $searchLayer = $this->searchLayerFactory->create();

        return $searchLayer->getProductCollection()->setPageSize(1)->getSize() === 0;
    }

    /**
     * Return cacheable result
     *
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Magento\Search\Model\Query $query
     * @return void
     */
    private function getCacheableResult($catalogSearchHelper, $query)
    {
        if (!$catalogSearchHelper->isMinQueryLength()) {
            $redirect = $query->getRedirect();
            if ($redirect && $this->_url->getCurrentUrl() !== $redirect) {
                $this->getResponse()->setRedirect($redirect);
                return;
            }
        }

        $catalogSearchHelper->checkNotes();

        $this->_view->loadLayout($this->getLayoutHandles($query));
        $this->_view->renderLayout();
    }

    /**
     * Return not cacheable result
     *
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Magento\Search\Model\Query $query
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getNotCacheableResult($catalogSearchHelper, $query)
    {
        if ($catalogSearchHelper->isMinQueryLength()) {
            $query->setId(0)->setIsActive(1)->setIsProcessed(1);
        } else {
            $query->saveIncrementalPopularity();
            $redirect = $query->getRedirect();
            if ($redirect && $this->_url->getCurrentUrl() !== $redirect) {
                $this->getResponse()->setRedirect($redirect);
                return;
            }
        }

        $catalogSearchHelper->checkNotes();

        $this->_view->loadLayout($this->getLayoutHandles($query));
        $this->getResponse()->setNoCacheHeaders();
        $this->_view->renderLayout();
    }

    /**
     * Checks for toolbar actions
     *
     * @return bool
     */
    private function shouldRedirectOnToolbarAction(): bool
    {
        $params = $this->getRequest()->getParams();

        return $this->toolbarMemorizer->isMemorizingAllowed() && empty(array_intersect([
                Toolbar::ORDER_PARAM_NAME,
                Toolbar::DIRECTION_PARAM_NAME,
                Toolbar::MODE_PARAM_NAME,
                Toolbar::LIMIT_PARAM_NAME
            ], array_keys($params))) === false;
    }
}
