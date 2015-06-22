<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\Resource\Db\VersionControl;

/**
 * Class Metadata represents a list of entity fields that are applicable for persistence operations
 */
class Metadata
{
    /**
     * @var array
     */
    protected $metadataInfo = [];

    /**
     * Returns list of entity fields that are applicable for persistence operations
     *
     * @param \Magento\Framework\Object $entity
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFields(\Magento\Framework\Object $entity)
    {
        if (!isset($this->metadataInfo[get_class($entity)])) {
            $this->metadataInfo[get_class($entity)] =
                $entity->getResource()->getReadConnection()->describeTable(
                    $entity->getResource()->getMainTable()
                );
        }
        return $this->metadataInfo[get_class($entity)];
    }
}
