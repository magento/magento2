<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\Entity\Action;

use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\ExtensionPool;

/**
 * Class ReadExtension
 */
class ReadExtension
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @var ExtensionPool
     */
    protected $extensionPool;

    /**
     * @param MetadataPool $metadataPool
     * @param ExtensionPool $extensionPool
     */
    public function __construct(
        MetadataPool $metadataPool,
        ExtensionPool $extensionPool
    ) {
        $this->metadataPool = $metadataPool;
        $this->extensionPool = $extensionPool;
    }

    /**
     * @param string $entityType
     * @param object $entity
     * @return object
     * @throws \Exception
     */
    public function execute($entityType, $entity)
    {
        $hydrator = $this->metadataPool->getHydrator($entityType);
        $entityData = $hydrator->extract($entity);
        $actions = $this->extensionPool->getActions($entityType, 'read');
        foreach ($actions as $action) {
            $data = $action->execute($entityType, $entityData);
            $entity = $hydrator->hydrate($entity, $data);
        }
        return $entity;
    }
}
