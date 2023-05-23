<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Eav\Model\Entity\VersionControl;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;
use Magento\Framework\Model\ResourceModel\Db\VersionControl\Metadata as ResourceModelMetaData;

/**
 * Class Metadata represents a list of entity fields that are applicable for persistence operations
 */
class Metadata extends ResourceModelMetaData implements ResetAfterRequestInterface
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

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->metadataInfo = [];
    }
}
