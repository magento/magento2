<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Api\ProductLinkRepositoryInterface;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\Data\ProductLinkInterface;
use Magento\Catalog\Model\Indexer\Product\Full as FullProductIndexer;

/**
 * Product reindexing after save links plugin.
 */
class ReindexAfterSaveProductLinksPlugin
{
    /**
     * @var FullProductIndexer
     */
    private $fullProductIndexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param FullProductIndexer $fullProductIndexer
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(FullProductIndexer $fullProductIndexer, ProductRepositoryInterface $productRepository)
    {
        $this->fullProductIndexer = $fullProductIndexer;
        $this->productRepository = $productRepository;
    }

    /**
     * Complex reindex after product links has been saved.
     *
     * @param ProductLinkRepositoryInterface $subject
     * @param bool $result
     * @param ProductLinkInterface $entity
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(ProductLinkRepositoryInterface $subject, bool $result, ProductLinkInterface $entity): bool
    {
        $product = $this->productRepository->get($entity->getSku());
        $this->fullProductIndexer->executeRow($product->getId());

        return $result;
    }
}
