<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogAdminUi\Controller\Adminhtml\Source;

use Magento\AsynchronousOperations\Model\MassSchedule;
use Magento\Backend\App\Action;
use Magento\Backend\Model\Auth;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\BulkException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryCatalogAdminUi\Model\BulkOperationsConfig;
use Magento\InventoryCatalogAdminUi\Model\BulkSessionProductsStorage;
use Magento\InventoryCatalogApi\Api\BulkSourceAssignInterface;
use Psr\Log\LoggerInterface;

class BulkAssignPost extends Action
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
     * @var BulkSourceAssignInterface
     */
    private $bulkSourceAssign;

    /**
     * @var MassSchedule
     */
    private $massSchedule;

    /**
     * @var Auth
     */
    private $authSession;

    /**
     * @var BulkOperationsConfig
     */
    private $bulkOperationsConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Action\Context $context
     * @param BulkSourceAssignInterface $bulkSourceAssign
     * @param BulkSessionProductsStorage $bulkSessionProductsStorage
     * @param BulkOperationsConfig $bulkOperationsConfig
     * @param MassSchedule $massSchedule
     * @param LoggerInterface $logger
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        Action\Context $context,
        BulkSourceAssignInterface $bulkSourceAssign,
        BulkSessionProductsStorage $bulkSessionProductsStorage,
        BulkOperationsConfig $bulkOperationsConfig,
        MassSchedule $massSchedule,
        LoggerInterface $logger
    ) {
        parent::__construct($context);

        $this->bulkSessionProductsStorage = $bulkSessionProductsStorage;
        $this->bulkSourceAssign = $bulkSourceAssign;
        $this->massSchedule = $massSchedule;
        $this->authSession = $context->getAuth();
        $this->bulkOperationsConfig = $bulkOperationsConfig;
        $this->logger = $logger;
    }

    /**
     * @param array $skus
     * @param array $sourceCodes
     * @return void
     * @throws ValidationException
     */
    private function runSynchronousOperation(array $skus, array $sourceCodes): void
    {
        $count = $this->bulkSourceAssign->execute($skus, $sourceCodes);
        $this->messageManager->addSuccessMessage(__('Bulk operation was successful: %count assignments.', [
            'count' => $count
        ]));
    }

    /**
     * @param array $skus
     * @param array $sourceCodes
     * @return void
     * @throws BulkException
     * @throws LocalizedException
     */
    private function runAsynchronousOperation(array $skus, array $sourceCodes): void
    {
        $batchSize = $this->bulkOperationsConfig->getBatchSize();
        $userId = (int) $this->authSession->getUser()->getId();

        $skusChunks = array_chunk($skus, $batchSize);
        $operations = [];
        foreach ($skusChunks as $skuChunk) {
            $operations[] = [
                'skus' => $skuChunk,
                'sourceCodes' => $sourceCodes,
            ];
        }

        $this->massSchedule->publishMass(
            'async.V1.inventory.bulk-product-source-assign.POST',
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
        $sourceCodes = $this->getRequest()->getParam('sources', []);
        $skus = $this->bulkSessionProductsStorage->getProductsSkus();

        $async = $this->bulkOperationsConfig->isAsyncEnabled();

        try {
            if ($async) {
                $this->runAsynchronousOperation($skus, $sourceCodes);
            } else {
                $this->runSynchronousOperation($skus, $sourceCodes);
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            $this->messageManager->addErrorMessage(__('Something went wrong during the operation.'));
        }

        $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $result->setPath('catalog/product/index');
    }
}
