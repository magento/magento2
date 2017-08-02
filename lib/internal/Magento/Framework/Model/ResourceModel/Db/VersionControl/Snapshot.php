<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

/**
 * Class Snapshot register snapshot of entity data, for tracking changes
 * @since 2.0.0
 */
class Snapshot
{
    /**
     * Array of snapshots of entities data
     *
     * @var array
     * @since 2.0.0
     */
    protected $snapshotData = [];

    /**
     * @var Metadata
     * @since 2.0.0
     */
    protected $metadata;

    /**
     * Initialization
     *
     * @param Metadata $metadata
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
}
