<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller\Result;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
<<<<<<< HEAD
=======
use Magento\Framework\App\Action\HttpPostActionInterface;
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Search\Model\PopularSearchTerms;

/**
 * Search result.
 */
class Index extends \Magento\Framework\App\Action\Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * No results default handle.
     */
    const DEFAULT_NO_RESULT_HANDLE = 'catalogsearch_result_index_noresults';

    /**
     * Catalog session
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
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     */
    public function __construct(
        Context $context,
        Session $catalogSession,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        Resolver $layerResolver
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_queryFactory = $queryFactory;
        $this->layerResolver = $layerResolver;
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

        if ($queryText != '') {
            $catalogSearchHelper = $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class);

            $getAdditionalRequestParameters = $this->getRequest()->getParams();
            unset($getAdditionalRequestParameters[QueryFactory::QUERY_VAR_NAME]);

<<<<<<< HEAD
            if (empty($getAdditionalRequestParameters) &&
                $this->_objectManager->get(PopularSearchTerms::class)->isCacheable($queryText, $storeId)
            ) {
                $this->getCacheableResult($catalogSearchHelper, $query);
            } else {
                $this->getNotCacheableResult($catalogSearchHelper, $query);
=======
            $handles = null;
            if ($query->getNumResults() == 0) {
                $this->_view->getPage()->initLayout();
                $handles = $this->_view->getLayout()->getUpdate()->getHandles();
                $handles[] = static::DEFAULT_NO_RESULT_HANDLE;
            }

            if (empty($getAdditionalRequestParameters) &&
                $this->_objectManager->get(PopularSearchTerms::class)->isCacheable($queryText, $storeId)
            ) {
                $this->getCacheableResult($catalogSearchHelper, $query, $handles);
            } else {
                $this->getNotCacheableResult($catalogSearchHelper, $query, $handles);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
            }
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }

    /**
     * Return cacheable result
     *
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Magento\Search\Model\Query $query
<<<<<<< HEAD
     * @return void
     */
    private function getCacheableResult($catalogSearchHelper, $query)
=======
     * @param array $handles
     * @return void
     */
    private function getCacheableResult($catalogSearchHelper, $query, $handles)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    {
        if (!$catalogSearchHelper->isMinQueryLength()) {
            $redirect = $query->getRedirect();
            if ($redirect && $this->_url->getCurrentUrl() !== $redirect) {
                $this->getResponse()->setRedirect($redirect);
                return;
            }
        }

        $catalogSearchHelper->checkNotes();
<<<<<<< HEAD

        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

=======

        $this->_view->loadLayout($handles);
        $this->_view->renderLayout();
    }

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
    /**
     * Return not cacheable result
     *
     * @param \Magento\CatalogSearch\Helper\Data $catalogSearchHelper
     * @param \Magento\Search\Model\Query $query
<<<<<<< HEAD
=======
     * @param array $handles
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
     * @return void
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
<<<<<<< HEAD
    private function getNotCacheableResult($catalogSearchHelper, $query)
=======
    private function getNotCacheableResult($catalogSearchHelper, $query, $handles)
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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

<<<<<<< HEAD
        $this->_view->loadLayout();
=======
        $this->_view->loadLayout($handles);
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
        $this->getResponse()->setNoCacheHeaders();
        $this->_view->renderLayout();
    }
}
