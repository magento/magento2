<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Class Snapshot register snapshot of entity data, for tracking changes
 */
class Snapshot implements ResetAfterRequestInterface
{
    /**
     * Array of snapshots of entities data
     *
     * @var array
     */
    protected $snapshotData = [];

    /**
     * @var Metadata
     */
    protected $metadata;

    /**
     * Initialization
     *
     * @param Metadata $metadata
     */
    public function __construct(
        Metadata $metadata
    ) {
        $this->metadata = $metadata;
    }

    /**
     * Register snapshot of entity data, for tracking changes
     *
     * @param \Magento\Framework\DataObject $entity
     * @return void
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function registerSnapshot(\Magento\Framework\DataObject $entity)
    {
        $metaData = $this->metadata->getFields($entity);
        $filteredData = array_intersect_key($entity->getData(), $metaData);
        $data = array_merge($metaData, $filteredData);
        $this->snapshotData[get_class($entity)][$entity->getId()] = $data;
    }

    /**
     * Check is current entity has changes, by comparing current object state with stored snapshot
     *
     * @param \Magento\Framework\DataObject $entity
     * @return bool
     */
    public function isModified(\Magento\Framework\DataObject $entity)
    {
        if (!$entity->getId()) {
            return true;
        }

        $entityClass = get_class($entity);
        if (!isset($this->snapshotData[$entityClass][$entity->getId()])) {
            return true;
        }
        foreach ($this->snapshotData[$entityClass][$entity->getId()] as $field => $value) {
            if ($entity->getDataByKey($field) != $value) {
                return true;
            }
        }

        return false;
    }

    /**
     * Clear snapshot data
     *
     * @param \Magento\Framework\DataObject|null $entity
     */
    public function clear(\Magento\Framework\DataObject $entity = null)
    {
        if ($entity !== null) {
            $this->snapshotData[get_class($entity)] = [];
        } else {
            $this->snapshotData = [];
        }
    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->snapshotData = [];
    }
}
