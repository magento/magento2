<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Bundle\Plugin\Api\ProductLinkManagement;

use Magento\Bundle\Api\ProductLinkManagementInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Indexer\Product\Full;

/**
 * Reindex bundle product after child has been added.
 */
class ReindexAfterAddChildPlugin
{
    /**
     * @var Full
     */
    private $indexer;

    /**
     * @param Full $indexer
     */
    public function __construct(Full $indexer)
    {
        $this->indexer = $indexer;
    }

    /**
     * Reindex bundle product after child has been added.
     *
     * @param ProductLinkManagementInterface $subject
     * @param int $result
     * @param ProductInterface $bundleProduct
     * @return int
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddChild(
        ProductLinkManagementInterface $subject,
        int $result,
        ProductInterface $bundleProduct
    ): int {
        $this->indexer->executeRow($bundleProduct->getId());

        return $result;
    }
}
