<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Model\ResourceModel\Db\VersionControl;

/**
 * Class Metadata represents a list of entity fields that are applicable for persistence operations
 * @since 2.0.0
 */
class Metadata
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $metadataInfo = [];

    /**
     * Returns list of entity fields that are applicable for persistence operations
     *
     * @param \Magento\Framework\DataObject $entity
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function getFields(\Magento\Framework\DataObject $entity)
    {
        $entityClass = get_class($entity);
        if (!isset($this->metadataInfo[$entityClass])) {
            $this->metadataInfo[$entityClass] =
                array_fill_keys(
                    array_keys(
                        $entity->getResource()->getConnection()->describeTable(
                            $entity->getResource()->getMainTable()
                        )
                    ),
                    null
                );
        }
        return $this->metadataInfo[$entityClass];
    }
}
