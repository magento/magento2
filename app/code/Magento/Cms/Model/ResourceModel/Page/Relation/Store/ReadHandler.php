<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Model\ResourceModel\Page\Relation\Store;

use Magento\Cms\Model\ResourceModel\Page;
use Magento\Framework\Model\Entity\MetadataPool;

class ReadHandler
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var Page
     */
    protected $resourcePage;

    /**
     * @param MetadataPool $metadataPool
     * @param Page $resourcePage
     */
    public function __construct(
        MetadataPool $metadataPool,
        Page $resourcePage
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourcePage = $resourcePage;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute($entityType, $entity)
    {
        if ($entity->getId()) {
            $stores = $this->resourcePage->lookupStoreIds((int)$entity->getId());
            $entity->setData('store_id', $stores);
        }
        return $entity;
    }
}
