<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Model\ResourceModel\Db;

use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class ReadEntityRow
 * @since 2.1.0
 */
class ReadEntityRow
{
    /**
     * @var MetadataPool
     * @since 2.1.0
     */
    protected $metadataPool;

    /**
     * @param MetadataPool $metadataPool
     * @since 2.1.0
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
     * @since 2.1.0
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
