<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Controller\SearchTermsLog;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\CatalogSearch\Helper\Data as HelperData;
use Magento\Framework\Controller\Result\Json;

/**
 * Controller for save search terms
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
class Save extends \Magento\Framework\App\Action\Action
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Catalog search helper
     *
     * @var HelperData
     */
    private $catalogSearchHelper;

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @param Context $context
     * @param HelperData $catalogSearchHelper
     * @param StoreManagerInterface $storeManager
     * @param QueryFactory $queryFactory
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        HelperData $catalogSearchHelper,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        JsonFactory $resultJsonFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->catalogSearchHelper = $catalogSearchHelper;
        $this->queryFactory = $queryFactory;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    /**
     * Save search term
     *
     * @return Json
     */
    public function execute()
    {
        /* @var $query \Magento\Search\Model\Query */
        $query = $this->queryFactory->get();

        $query->setStoreId($this->storeManager->getStore()->getId());

        if ($query->getQueryText() != '') {
            try {
                if ($this->catalogSearchHelper->isMinQueryLength()) {
                    $query->setId(0)->setIsActive(1)->setIsProcessed(1);
                } else {
                    $query->saveIncrementalPopularity();
                }
                $responseContent = ['success' => true, 'error_message' => ''];
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $responseContent = ['success' => false, 'error_message' => $e];
            }
        } else {
            $responseContent = ['success' => false, 'error_message' => __('Search term is empty')];
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($responseContent);
    }
}
