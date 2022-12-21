<?php

/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Bundle\Plugin\Api\ProductLinkManagement;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Indexer\Product\Full;

/**
 * Reindex bundle product after child has been removed.
 */
class ReindexAfterRemoveChildPlugin
{
    /**
     * @var Full
     */
    private $indexer;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @param Full $indexer
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(Full $indexer, ProductRepositoryInterface $productRepository)
    {
        $this->indexer = $indexer;
        $this->productRepository = $productRepository;
    }

    /**
     * Reindex bundle product after child has been removed.
     *
     * @param ProductLinkManagementInterface $subject
     * @param bool $result
     * @param string $sku
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterRemoveChild(
        ProductLinkManagementInterface $subject,
        bool $result,
        string $sku
    ): bool {
        $bundleProduct = $this->productRepository->get($sku, true);
        $this->indexer->executeRow($bundleProduct->getId());

        return $result;
    }
}
