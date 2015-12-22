<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Relation\ActionPool as RelationActionPool;

/**
 * Class CreateRelation
 */
class DeleteRelation
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var RelationActionPool
     */
    protected $relationActionPool;

    /**
     * @param MetadataPool $metadataPool
     * @param RelationActionPool $relationActionPool
     */
    public function __construct(
        MetadataPool $metadataPool,
        RelationActionPool $relationActionPool
    ) {
        $this->metadataPool = $metadataPool;
        $this->relationActionPool = $relationActionPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $actions = $this->relationActionPool->getActions($entityType, 'delete');
        foreach ($actions as $action) {
            $entity = $action->execute($entityType, $entity);
        }
        return $entity;
    }
}
