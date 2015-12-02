<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model\ResourceModel;

use Magento\CatalogRule\Model\ResourceModel\Rule;
use Magento\Framework\Model\Entity\MetadataPool;

class ReadHandler
{
    /**
     * @var Rule
     */
    protected $ruleResource;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Rule $ruleResource,
        MetadataPool $metadataPool
    ) {
        $this->ruleResource = $ruleResource;
        $this->metadataPool = $metadataPool;
    }

    public function execute($entityType, $entity)
    {
        $linkField = $this->metadataPool->getMetadata($entityType)->getLinkField();
        $entityId = $entity[$linkField];

        $entity['customer_group_ids'] = $this->ruleResource->getCustomerGroupIds($entityId);
        $entity['website_ids'] = $this->ruleResource->getWebsiteIds($entityId);

        return $entity;
    }
}
