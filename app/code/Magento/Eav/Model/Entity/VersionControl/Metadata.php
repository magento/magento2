<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\VersionControl;

/**
 * Class Metadata represents a list of entity fields that are applicable for persistence operations
 */
class Metadata extends \Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata
{
    /**
     * Returns list of entity fields that are applicable for persistence operations
     *
     * @param \Magento\Framework\DataObject $entity
     * @return array
     */
    public function getFields(\Magento\Framework\DataObject $entity)
    {
        $entityClass = get_class($entity);
        if (!isset($this->metadataInfo[$entityClass])) {
            $fields = $entity->getResource()->getConnection()->describeTable(
                $entity->getResource()->getEntityTable()
            );

            $fields = array_merge($fields, $entity->getAttributes());

            $fields = array_fill_keys(
                array_keys($fields),
                null
            );

            $this->metadataInfo[$entityClass] = $fields;
        }

        return $this->metadataInfo[$entityClass];
    }
}
