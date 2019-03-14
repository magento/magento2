<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Inventory;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogAdminUi\Model\BulkOperationsConfig;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\BulkInventoryTransferInterface;
use Psr\Log\LoggerInterface;

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
     * @var BulkOperationsConfig
     */
    private $bulkOperationsConfig;

    /**
     * @var Auth
     */
    private $authSession;

    /**
     * @var MassSchedule
     */
    private $massSchedule;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param BulkInventoryTransferInterface $bulkInventoryTransfer
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @param BulkOperationsConfig $bulkOperationsConfig
     * @param LoggerInterface $logger
     * @param MassSchedule $massSchedule
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        BulkInventoryTransferInterface $bulkInventoryTransfer,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        BulkOperationsConfig $bulkOperationsConfig,
        LoggerInterface $logger,
        MassSchedule $massSchedule
    ) {
        parent::__construct($context);

        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkInventoryTransfer = $bulkInventoryTransfer;
        $this->authSession = $context->getAuth();
        $this->bulkOperationsConfig = $bulkOperationsConfig;
        $this->massSchedule = $massSchedule;
        $this->logger = $logger;
    }

    /**
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignSource
     * @return void
     * @throws ValidationException
     */
    private function runSynchronousOperation(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignSource
    ): void {
        $count = $this->bulkInventoryTransfer->execute($skus, $originSource, $destinationSource, $unassignSource);
        $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count inventory transfers.', [
            'count' => $count
        ]));
    }

    /**
     * @param array $skus
     * @param string $originSource
     * @param string $destinationSource
     * @param bool $unassignSource
     * @return void
     * @throws \Magento\Framework\Exception\BulkException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function runAsynchronousOperation(
        array $skus,
        string $originSource,
        string $destinationSource,
        bool $unassignSource
    ): void {
        $batchSize = $this->bulkOperationsConfig->getBatchSize();
        $userId = (int) $this->authSession->getUser()->getId();

        $skusChunks = array_chunk($skus, $batchSize);
        $operations = [];
        foreach ($skusChunks as $skuChunk) {
            $operations[] = [
                'skus' => $skuChunk,
                'originSource' => $originSource,
                'destinationSource' => $destinationSource,
                'unassignFromOrigin' => $unassignSource,
            ];
        }

        $this->massSchedule->publishMass(
            'async.V1.inventory.bulk-product-source-transfer.POST',
            $operations,
            null,
            $userId
        );

        $this->messageManager->addSuccessMessage(__('Your request was successfully queued for asynchronous execution'));
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $originSource = $this->getRequest()->getParam('origin_source', '');
        $destinationSource = $this->getRequest()->getParam('destination_source', '');

        $skus = $this->bulkSessionProductsStorage->getProductsSkus();
        $unassignSource = (bool) $this->getRequest()->getParam('unassign_origin_source', false);

        $async = $this->bulkOperationsConfig->isAsyncEnabled();

        try {
            if ($async) {
                $this->runAsynchronousOperation($skus, $originSource, $destinationSource, $unassignSource);
            } else {
                $this->runSynchronousOperation($skus, $originSource, $destinationSource, $unassignSource);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong during the operation.'));
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }
}
