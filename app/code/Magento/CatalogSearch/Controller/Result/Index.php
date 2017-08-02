<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller\Result;

use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

/**
 * Class \Magento\CatalogSearch\Controller\Result\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Catalog session
     *
     * @var Session
     * @since 2.0.0
     */
    protected $_catalogSession;

    /**
     * @var StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var QueryFactory
     * @since 2.0.0
     */
    private $_queryFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     * @since 2.0.0
     */
    private $layerResolver;

    /**
     * @param Context $context
     * @param Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param Resolver $layerResolver
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function execute()
    {
        $this->layerResolver->create(Resolver::CATALOG_LAYER_SEARCH);
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->_queryFactory->get();

        $query->setStoreId($this->_storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            if ($this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();

                $redirect = $query->getRedirect();
                if ($redirect && $this->_url->getCurrentUrl() !== $redirect) {
                    $this->getResponse()->setRedirect($redirect);
                    return;
                }
            }

            $this->_objectManager->get(\Magento\CatalogSearch\Helper\Data::class)->checkNotes();

            $this->_view->loadLayout();
            $this->_view->renderLayout();
        } else {
            $this->getResponse()->setRedirect($this->_redirect->getRedirectUrl());
        }
    }
}
