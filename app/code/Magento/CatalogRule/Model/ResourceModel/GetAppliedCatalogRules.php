<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\CatalogRule\Api\Data\RuleInterface;

class GetAppliedCatalogRules
{
    /**
     * GetAppliedCatalogRules constructor
     *
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Get applied catalog rules
     *
     * @param int $productId
     * @param int $websiteId
     * @return array
     */
    public function execute(int $productId, int $websiteId): array
    {
        $connection = $this->resourceConnection->getConnection();
        $linkField = $this->metadataPool->getMetadata(RuleInterface::class)->getLinkField();

        return $connection->fetchAll(
            $connection->select()
                ->from(
                    ['cr' => $this->resourceConnection->getTableName('catalogrule')],
                    ['name']
                )
                ->join(
                    ['crp' => $this->resourceConnection->getTableName('catalogrule_product')],
                    'crp.rule_id = cr.rule_id',
                )
                ->join(
                    ['crw' => $this->resourceConnection->getTableName('catalogrule_website')],
                    "cr.rule_id = crw.$linkField",
                )
                ->reset('columns')
                ->columns(['name'])
                ->distinct(true)
                ->where('cr.is_active = ?', 1)
                ->where('crp.product_id = ?', $productId)
                ->where('crw.website_id = ?', $websiteId)
        ) ?? [];
    }
}
