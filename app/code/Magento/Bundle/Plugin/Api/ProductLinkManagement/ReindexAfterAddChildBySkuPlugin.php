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
 * Reindex bundle product after child has been added.
 */
class ReindexAfterAddChildBySkuPlugin
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
     * Reindex bundle product after child has been added.
     *
     * @param ProductLinkManagementInterface $subject
     * @param int $result
     * @param string $sku
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddChildByProductSku(
        ProductLinkManagementInterface $subject,
        int $result,
        string $sku
    ): int {
        $bundleProduct = $this->productRepository->get($sku, true);
        $this->indexer->executeRow($bundleProduct->getId());

        return $result;
    }
}
