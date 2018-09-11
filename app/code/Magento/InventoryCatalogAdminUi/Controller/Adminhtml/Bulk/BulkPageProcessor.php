<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Bulk;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\Phrase;

/**
 * Bulk process page for assign, unassign and transfer sources.
 */
class BulkPageProcessor
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var BulkSessionProductsStorage
     */
    private $bulkSessionProductsStorage;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @param ResultFactory $resultFactory
     * @param ManagerInterface $messageManager
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Filter $filter,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * @param Phrase $title
     * @return ResponseInterface|ResultInterface
     */
    public function execute(Phrase $title)
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('catalog/product/index');
        }

        $this->bulkSessionProductsStorage->setProductsSkus($collection->getColumnValues('sku'));

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
