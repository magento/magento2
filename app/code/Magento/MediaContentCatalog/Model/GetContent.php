<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Framework\App\ResourceConnection;

/**
 * Get concatenated content for all store views
 */
class GetContent
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        ResourceConnection $resourceConnection
    ) {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get concatenated content for all store views
     *
     * @param int $entityId
     * @param AbstractAttribute $attribute
     * @return string
     */
    public function execute(int $entityId, AbstractAttribute $attribute): string
    {
        return implode(
            PHP_EOL,
            $this->getDistinctContent(
                $entityId,
                (int) $attribute->getAttributeId(),
                $attribute->getBackendTable()
            )
        );
    }

    /**
     * Load values of an attribute for all store views
     *
     * @param int $entityId
     * @param int $attributeId
     * @return array
     */
    private function getDistinctContent(int $entityId, int $attributeId, string $table): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            $table,
            'value'
        )->where(
            'attribute_id = ?',
            $attributeId
        )->where(
            'entity_id = ?',
            $entityId
        )->distinct(true);

        return $connection->fetchCol($select);
    }
}
