<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model;

use Magento\AsynchronousOperations\Api\Data\OperationInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\DB\Adapter\DeadlockException;
use Magento\Framework\DB\Adapter\LockWaitException;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Store;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Psr\Log\LoggerInterface;

/**
 *  Product url rewrites updater.
 */
class UpdateProductUrlRewrite
{
    /**
     * @param EntityManager $entityManager
     * @param SerializerInterface $serializer
     * @param LoggerInterface $logger
     * @param UrlPersistInterface $urlPersist
     * @param CollectionFactory $productCollectionFactory
     * @param ProductUrlRewriteGenerator $productUrlRewriteGenerator
     * @param int $batchSize
     */
    public function __construct(
        private EntityManager $entityManager,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
        private UrlPersistInterface $urlPersist,
        private CollectionFactory $productCollectionFactory,
        private ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        private int $batchSize = 5000
    ) {
    }

    /**
     * Process generation of url rewrites for products.
     *
     * @param OperationInterface $operation
     * @return void
     */
    public function process(OperationInterface $operation): void
    {
        try {
            $serializedData = $operation->getSerializedData();
            $data = $this->serializer->unserialize($serializedData);
            $this->urlPersist->replace($this->generateProductUrls($data['website_id']));
            $operation->setStatus(OperationInterface::STATUS_TYPE_COMPLETE);
            $operation->setResultMessage(null);
        } catch (LockWaitException|DeadlockException $e) {
            $operation->setStatus(OperationInterface::STATUS_TYPE_RETRIABLY_FAILED);
            $operation->setErrorCode($e->getCode());
            $operation->setResultMessage($e->getMessage());
        } catch (LocalizedException $e) {
            $operation->setStatus(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
            $operation->setErrorCode($e->getCode());
            $operation->setResultMessage($e->getMessage());
        } catch (\Throwable $e) {
            $this->logger->critical($e);
            $operation->setStatus(OperationInterface::STATUS_TYPE_NOT_RETRIABLY_FAILED);
            $operation->setErrorCode($e->getCode());
            $operation->setResultMessage(
                __('Sorry, something went wrong during update synchronization. Please see log for details.')
            );
        }
        $this->entityManager->save($operation);
    }

    /**
     * Generate url rewrites for products assigned to website
     *
     * @param int $websiteId
     * @return array
     */
    private function generateProductUrls(int $websiteId): array
    {
        $urls = [];
        $collection = $this->productCollectionFactory->create()
            ->addCategoryIds()
            ->addAttributeToSelect(['name', 'url_path', 'url_key', 'visibility'])
            ->addWebsiteFilter([$websiteId]);

        $collection->setPageSize($this->batchSize);
        $pages = $collection->getLastPageNumber();

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $collection->setCurPage($currentPage);

            foreach ($collection as $product) {
                $product->setStoreId(Store::DEFAULT_STORE_ID);
                $urls[] = $this->productUrlRewriteGenerator->generate($product);
            }
            $collection->clear();
        }

        return array_merge([], ...$urls);
    }
}
