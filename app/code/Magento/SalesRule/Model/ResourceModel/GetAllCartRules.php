<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Model\ResourceModel;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\SalesRule\Api\Data\RuleInterface;

class GetAllCartRules
{
    /**
     * GetAllCartRules constructor
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
     * Get all active rule names
     *
     * @param ContextInterface $context
     * @return array
     * @throws Exception
     */
    public function execute(ContextInterface $context): array
    {
        $connection = $this->resourceConnection->getConnection();
        $linkField = $this->metadataPool->getMetadata(RuleInterface::class)->getLinkField();

        return $connection->fetchAll(
            $connection->select()
                ->from(['sr' => $this->resourceConnection->getTableName('salesrule')])
                ->reset('columns')
                ->columns(['name'])
                ->join(
                    ['srw' => $this->resourceConnection->getTableName('salesrule_website')],
                    "sr.rule_id = srw.$linkField",
                    []
                )
                ->where('sr.is_active = ?', 1)
                ->where(
                    'srw.website_id = ?',
                    (int)$context->getExtensionAttributes()->getStore()->getWebsiteId()
                )
        ) ?? [];
    }
}
