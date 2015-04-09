<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Resource;

use Magento\Sales\Model\AbstractModel;

/**
 * Class EntityMetadata represents a list of entity fields that are applicable for persistence operations
 */
class EntityMetadata
{
    /**
     * @var array
     */
    protected $metadataInfo = [];

    /**
     * Returns list of entity fields that are applicable for persistence operations
     *
     * @param AbstractModel $entity
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFields(AbstractModel $entity)
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
