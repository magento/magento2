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
use Magento\InventoryApi\Model\GetSourceCodesBySkusInterface;
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
     * @var GetSourceCodesBySkusInterface
     */
    private $getSourceCodesBySkus;

    /**
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @param ResultFactory $resultFactory
     * @param ManagerInterface $messageManager
     * @param GetSourceCodesBySkusInterface $getSourceCodesBySkus
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        Filter $filter,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        ResultFactory $resultFactory,
        ManagerInterface $messageManager,
        GetSourceCodesBySkusInterface $getSourceCodesBySkus
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->filter = $filter;
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->resultFactory = $resultFactory;
        $this->messageManager = $messageManager;
        $this->getSourceCodesBySkus = $getSourceCodesBySkus;
    }

    /**
     * @param Phrase $title
     * @param bool $verifyProductsAssignment
     * @return ResponseInterface|ResultInterface
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(Phrase $title, bool $verifyProductsAssignment)
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not create products collection.'));
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('catalog/product/index');
        }

        $skus = $collection->getColumnValues('sku');

        if ($verifyProductsAssignment) {
            $sourceCodes = $this->getSourceCodesBySkus->execute($skus);
            if (empty($sourceCodes)) {
                $this->messageManager->addErrorMessage(__('Selected products are not assigned to any source.'));
                $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $redirect->setPath('catalog/product/index');
            }
        }

        $this->bulkSessionProductsStorage->setProductsSkus($skus);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend($title);

        return $resultPage;
    }
}
