<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\ResourceModel;

use Exception;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\AttributeInterface;

/**
 * Class ReadHandler
 */
class ReadHandler implements AttributeInterface
{
    /**
     * @param Rule $ruleResource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        protected readonly Rule $ruleResource,
        protected readonly MetadataPool $metadataPool
    ) {
    }

    /**
     * Read handler
     *
     * @param string $entityType
     * @param array $entityData
     * @param array $arguments
     * @return array
     * @throws Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entityData, $arguments = [])
    {
        $linkField = $this->metadataPool->getMetadata($entityType)->getLinkField();
        $entityId = $entityData[$linkField];

        $entityData['customer_group_ids'] = $this->ruleResource->getCustomerGroupIds($entityId);
        $entityData['website_ids'] = $this->ruleResource->getWebsiteIds($entityId);

        return $entityData;
    }
}
