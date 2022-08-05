<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Model\Category;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\Store;

/**
 * Fetch category 'url_key' default value from the database.
 */
class GetDefaultUrlKey
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resourceConnection
     * @param EavConfig $eavConfig
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resourceConnection,
        EavConfig $eavConfig
    ) {
        $this->metadataPool = $metadataPool;
        $this->resourceConnection = $resourceConnection;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Retrieve 'url_key' value for default store.
     *
     * @param int $categoryId
     * @return string|null
     */
    public function execute(int $categoryId): ?string
    {
        $metadata = $this->metadataPool->getMetadata(CategoryInterface::class);
        $entityTypeId = $this->eavConfig->getEntityType(Category::ENTITY)->getId();
        $linkField = $metadata->getLinkField();
        $whereConditions = [
            'e.entity_type_id = ' . $entityTypeId,
            "e.attribute_code = 'url_key'",
            'c.' . $linkField . ' = ' . $categoryId,
            'c.store_id = ' . Store::DEFAULT_STORE_ID,
        ];
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from(['c' => $this->resourceConnection->getTableName('catalog_category_entity_varchar')])
            ->joinLeft(
                ['e' => $this->resourceConnection->getTableName('eav_attribute')],
                'e.attribute_id = c.attribute_id'
            )
            ->reset(Select::COLUMNS)
            ->columns(['c.value'])
            ->where(implode(' AND ', $whereConditions));

        return $connection->fetchOne($select) ?: null;
    }
}
