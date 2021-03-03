<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Plugin\Api\ProductLinkRepositoryInterface;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Full as FullProductIndexer;

/**
 * Product reindexing after delete by id links plugin.
 */
class ReindexAfterDeleteByIdProductLinksPlugin
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
     * Complex reindex after product links has been deleted.
     *
     * @param ProductLinkRepositoryInterface $subject
     * @param bool $result
     * @param string $sku
     * @param string $type
     * @param string $linkedProductSku
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterDeleteById(ProductLinkRepositoryInterface $subject, bool $result, $sku): bool
    {
        $product = $this->productRepository->get($sku);
        $this->fullProductIndexer->executeRow($product->getId());

        return $result;
    }
}
