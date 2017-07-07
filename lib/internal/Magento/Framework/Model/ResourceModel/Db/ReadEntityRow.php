<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class ReadEntityRow
 */
class ReadEntityRow
{
    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        MetadataPool $metadataPool
    ) {
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param string $entityType
     * @param string $identifier
     * @param array $context
     * @return array
     * @throws \Exception
     */
    public function execute($entityType, $identifier, $context = [])
    {
        $metadata = $this->metadataPool->getMetadata($entityType);
        $select = $metadata->getEntityConnection()->select()
            ->from(['t' => $metadata->getEntityTable()])
            ->where($metadata->getIdentifierField() . ' = ?', $identifier);
        foreach ($context as $field => $value) {
            $select->where(
                $metadata->getEntityConnection()->quoteIdentifier($field) . ' = ?',
                $value
            );
        }
        $data = $metadata->getEntityConnection()->fetchRow($select);
        return $data ?: [];
    }
}
