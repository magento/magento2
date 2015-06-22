<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Db\VersionControl;

/**
 * Class Snapshot register snapshot of entity data, for tracking changes
 */
class Snapshot
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
     * @param \Magento\Framework\Object $entity
     * @return void
     */
    public function registerSnapshot(\Magento\Framework\Object $entity)
    {
        $data = [];

        foreach ($this->metadata->getFields($entity) as $field => $value) {
            if ($entity->hasData($field)) {
                $data[$field] = $entity->getData($field);
            } else {
                $data[$field] = null;
            }
        }

        $this->snapshotData[get_class($entity)][$entity->getId()] = $data;
    }

    /**
     * Check is current entity has changes, by comparing current object state with stored snapshot
     *
     * @param \Magento\Framework\Object $entity
     * @return bool
     */
    public function isModified(\Magento\Framework\Object $entity)
    {
        if (!$entity->getId()) {
            return true;
        }

        if (!isset($this->snapshotData[get_class($entity)][$entity->getId()])) {
            return true;
        }

        $data = array_intersect_key($entity->getData(), $this->metadata->getFields($entity));

        foreach ($data as $field => $value) {
            if ($this->snapshotData[get_class($entity)][$entity->getId()][$field] != $value) {
                return true;
            }
        }

        return false;
    }
}
