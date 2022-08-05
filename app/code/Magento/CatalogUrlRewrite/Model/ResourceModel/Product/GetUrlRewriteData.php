<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Config;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Fetch product url rewrite data from database
 */
class GetUrlRewriteData
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $connection
     * @param Config $eavConfig
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $connection,
        Config $eavConfig
    ) {
        $this->metadataPool = $metadataPool;
        $this->resource = $connection;
        $this->eavConfig = $eavConfig;
    }

    /**
     * Fetches product store data required for url key generation
     *
     * @param ProductInterface $product
     * @return array
     */
    public function execute(ProductInterface $product): array
    {
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $metadata->getLinkField();
        $connection = $this->resource->getConnection();
        $visibilityAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'visibility');
        $urlKeyAttribute = $this->eavConfig->getAttribute(Product::ENTITY, 'url_key');
        $visibilitySelect = $connection->select()
            ->from(['visibility' => $visibilityAttribute->getBackendTable()])
            ->joinRight(
                ['url_key' => $urlKeyAttribute->getBackendTable()],
                'url_key.' . $linkField . ' = visibility.' . $linkField . ' AND url_key.store_id = visibility.store_id'
                . ' AND url_key.attribute_id = ' . $urlKeyAttribute->getId(),
                ['url_key.value as url_key']
            )
            ->reset(Select::COLUMNS)
            ->columns(['url_key.store_id', 'url_key.value AS url_key', 'visibility.value AS visibility'])
            ->where('visibility.' . $linkField . ' = ?', $product->getData($linkField))
            ->where('visibility.attribute_id = ?', $visibilityAttribute->getId());
        $urlKeySelect = $connection->select()
            ->from(['url_key' => $urlKeyAttribute->getBackendTable()])
            ->joinLeft(
                ['visibility' => $visibilityAttribute->getBackendTable()],
                'url_key.' . $linkField . ' = visibility.' . $linkField . ' AND url_key.store_id = visibility.store_id'
                . ' AND visibility.attribute_id = ' . $visibilityAttribute->getId(),
                ['visibility.value as visibility']
            )
            ->reset(Select::COLUMNS)
            ->columns(['url_key.store_id', 'url_key.value AS url_key', 'visibility.value as visibility'])
            ->where('url_key.' . $linkField . ' = ?', $product->getData($linkField))
            ->where('url_key.attribute_id = ?', $urlKeyAttribute->getId());

        $select = $connection->select()->union([$visibilitySelect, $urlKeySelect], Select::SQL_UNION);

        return $connection->fetchAll($select);
    }
}
