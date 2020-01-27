<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Attribute\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\TemporaryStateExceptionInterface;
use Magento\Framework\Bulk\OperationInterface;

/**
 * Consumer for export message.
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Consumer
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Flat\Processor
     */
    private $productFlatIndexerProcessor;

    /**
     * @var \Magento\Catalog\Model\Indexer\Product\Price\Processor
     */
    private $productPriceIndexerProcessor;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;

    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    private $productAction;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Bulk\OperationManagementInterface
     */
    private $operationManagement;
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor
     * @param \Magento\Framework\Bulk\OperationManagementInterface $operationManagement
     * @param \Magento\Catalog\Model\Product\Action $action
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param EntityManager $entityManager
     */
    public function __construct(
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Catalog\Model\Indexer\Product\Flat\Processor $productFlatIndexerProcessor,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $productPriceIndexerProcessor,
        \Magento\Framework\Bulk\OperationManagementInterface $operationManagement,
        \Magento\Catalog\Model\Product\Action $action,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        EntityManager $entityManager
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->productFlatIndexerProcessor = $productFlatIndexerProcessor;
        $this->productPriceIndexerProcessor = $productPriceIndexerProcessor;
        $this->productAction = $action;
        $this->logger = $logger;
        $this->serializer = $serializer;
        $this->operationManagement = $operationManagement;
        $this->entityManager = $entityManager;
    }

    /**
     * Process
     *
     * @param \Magento\AsynchronousOperations\Api\Data\OperationInterface $operation
     * @throws \Exception
     *
     * @return void
     */
    public function process(\Magento\AsynchronousOperations\Api\Data\OperationInterface $operation)
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->execute($data);
        } catch (\Zend_Db_Adapter_Exception $e) {
            $this->logger->critical($e->getMessage());
            if ($e instanceof \Magento\Framework\DB\Adapter\LockWaitException
                || $e instanceof \Magento\Framework\DB\Adapter\DeadlockException
                || $e instanceof \Magento\Framework\DB\Adapter\ConnectionException
            ) {
                $status = OperationInterface::STATUS_TYPE_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message = $e->getMessage();
            } else {
                $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
                $errorCode = $e->getCode();
                $message = __(
                    'Sorry, something went wrong during product attributes update. Please see log for details.'
                );
            }
        } catch (NoSuchEntityException $e) {
            $this->logger->critical($e->getMessage());
            $status = ($e instanceof TemporaryStateExceptionInterface)
                ? OperationInterface::STATUS_TYPE_RETRIABLY_FAILED
                : OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = $e->getMessage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            $status = OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED;
            $errorCode = $e->getCode();
            $message = __('Sorry, something went wrong during product attributes update. Please see log for details.');
        }

        $operation->setStatus($status ?? OperationInterface::STATUS_TYPE_COMPLETE)
            ->setErrorCode($errorCode ?? null)
            ->setResultMessage($message ?? null);

        $this->entityManager->save($operation);
    }

    /**
     * Execute
     *
     * @param array $data
     *
     * @return void
     */
    private function execute($data): void
    {
        $this->productAction->updateAttributes($data['product_ids'], $data['attributes'], $data['store_id']);
        if ($this->catalogProduct->isDataForPriceIndexerWasChanged($data['attributes'])) {
            $this->productPriceIndexerProcessor->reindexList($data['product_ids']);
        }

        $this->productFlatIndexerProcessor->reindexList($data['product_ids']);
    }
}
