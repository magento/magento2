<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Source;

use Magento\Backend\App\Action;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Model\GetSourceCodesBySkusInterface;
use Magento\Ui\Component\MassAction\Filter;

class BulkUnassign extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

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
     * @var GetSourceCodesBySkusInterface
     */
    private $getSourceCodesBySkus;

    /**
     * @param Action\Context $context
     * @param CollectionFactory $collectionFactory
     * @param Filter $filter
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @param GetSourceCodesBySkusInterface $getSourceCodesBySkus
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        CollectionFactory $collectionFactory,
        Filter $filter,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        GetSourceCodesBySkusInterface $getSourceCodesBySkus
    ) {
        parent::__construct($context);

        $this->filter = $filter;
        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->collectionFactory = $collectionFactory;
        $this->getSourceCodesBySkus = $getSourceCodesBySkus;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not create products collection.'));
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('catalog/product/index');
        }

        $skus = $collection->getColumnValues('sku');
        if (empty($this->getSourceCodesBySkus->execute($skus))) {
            $this->messageManager->addErrorMessage(__('The products selection is not assigned to any store.'));
            $redirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $redirect->setPath('catalog/product/index');
        }

        $this->bulkSessionProductsStorage->setProductsSkus($skus);

        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->getConfig()->getTitle()->prepend(__('Bulk source unassignment'));

        return $resultPage;
    }
}
