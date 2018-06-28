<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Inventory;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;

class BulkTransferPost extends Action
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var BulkSessionProductsStorage
     */
    private $bulkSessionProductsStorage;

    /**
     * @var BulkInventoryTransferInterface
     */
    private $bulkInventoryTransfer;

    /**
     * @param Action\Context $context
     * @param BulkInventoryTransferInterface $bulkInventoryTransfer
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        BulkInventoryTransferInterface $bulkInventoryTransfer,
        BulkSessionProductsStorage $bulkSessionProductsStorage
    ) {
        parent::__construct($context);

        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkInventoryTransfer = $bulkInventoryTransfer;
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $sourceCode = $this->getRequest()->getParam('source', '');
        $skus = $this->bulkSessionProductsStorage->getProductsSkus();
        $defaultOnly = (bool) $this->getRequest()->getParam('default_source_only', false);

        try {
            $this->bulkInventoryTransfer->execute($skus, $sourceCode, $defaultOnly);
            $this->messageManager->addSuccessMessage(__('Bulk inventory transfer was successful.'));
        } catch (ValidationException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }
}
