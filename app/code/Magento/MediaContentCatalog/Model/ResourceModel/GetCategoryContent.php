<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\MediaContentCatalog\Model\ResourceModel;

use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Model\ResourceModel\Category;
use Magento\Framework\App\ResourceConnection;

/**
 * Get concatenated content for all store views
 */
class GetCategoryContent
{
    /**
     * @var Category
     */
    private $categoryResource;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param ResourceConnection $resourceConnection
     * @param Category $categoryResource
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        Category $categoryResource
    ) {
        $this->categoryResource = $categoryResource;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Get concatenated category content for all store views
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
     * Load values of an category attribute for all store views
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
        )->joinInner(
            ['rt' => $this->categoryResource->getEntityTable()],
            'rt.' . $attribute->getEntityIdField() . ' = abt.' . $attribute->getEntityIdField()
        )->where(
            'rt.entity_id = ?',
            $entityId
        )->where(
            $connection->quoteIdentifier('abt.attribute_id') . ' = ?',
            (int) $attribute->getAttributeId()
        );
        return $connection->fetchCol($select);
    }
}
