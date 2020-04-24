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
                $attribute
            )
        );
    }

    /**
     * Load values of an attribute for all store views
     *
     * @param int $entityId
     * @param AbstractAttribute $attribute
     * @return array
     */
    private function getDistinctContent(int $entityId, AbstractAttribute $attribute): array
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()->from(
            ['abt' => $attribute->getBackendTable()],
            'abt.value'
        )->where(
            $connection->quoteIdentifier('abt.attribute_id') . ' = ?',
            (int) $attribute->getAttributeId()
        )->where(
            $connection->quoteIdentifier('abt.' . $attribute->getEntityIdField()) . ' = ?',
            $entityId
        )->distinct(true);

        return $connection->fetchCol($select);
    }
}
