<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\InventoryImportExport\Model\Export;

use Magento\ImportExport\Model\Export\Entity\AbstractEntity;

/**
 * @inheritdoc
 */
class Sources extends AbstractEntity
{

    /**
     * Get header columns
     *
     * @return string[]
     */
    protected function _getHeaderColumns()
    {
        // TODO: Implement _getHeaderColumns() method.
    }

    /**
     * Get entity collection
     *
     * @param bool $resetCollection
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection($resetCollection = false)
    {
        // TODO: Implement _getEntityCollection() method.
    }

    /**
     * Export process.
     *
     * @return string
     */
    public function export()
    {
        // TODO: Implement export() method.
    }

    /**
     * Entity attributes collection getter.
     *
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getAttributeCollection()
    {
        // TODO: Implement getAttributeCollection() method.
    }

    /**
     * EAV entity type code getter.
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        // TODO: Implement getEntityTypeCode() method.
    }
}
