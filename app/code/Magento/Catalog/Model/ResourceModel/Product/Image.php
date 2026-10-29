<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Query\Generator;
use Magento\Framework\DB\Select;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\EntityManager\MetadataPool;

/**
 * Class for retrieval of all product images
 */
class Image
{
    /**
     * @var AdapterInterface
     */
    private $connection;

    /**
     * @var Generator
     */
    private $batchQueryGenerator;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var int
     */
    private $batchSize;

    /**
     * @param Generator $generator
     * @param ResourceConnection $resourceConnection
     * @param MetadataPool $metadataPool
     * @param int $batchSize
     */
    public function __construct(
        Generator $generator,
        ResourceConnection $resourceConnection,
        private readonly MetadataPool $metadataPool,
        $batchSize = 100
    ) {
        $this->batchQueryGenerator = $generator;
        $this->resourceConnection = $resourceConnection;
        $this->connection = $this->resourceConnection->getConnection();
        $this->batchSize = $batchSize;
    }

    /**
     * Get all product images.
     *
     * @return \Generator
     */
    public function getAllProductImages(): \Generator
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $this->getVisibleImagesSelect(),
            $this->batchSize,
            \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );

        foreach ($batchSelectIterator as $select) {
            foreach ($this->connection->fetchAll($select) as $key => $value) {
                yield $key => $value;
            }
        }
    }

    /**
     * Get used product images.
     *
     * @return \Generator
     */
    public function getUsedProductImages(): \Generator
    {
        $batchSelectIterator = $this->batchQueryGenerator->generate(
            'value_id',
            $this->getUsedImagesSelect(),
            $this->batchSize,
            \Magento\Framework\DB\Query\BatchIteratorInterface::NON_UNIQUE_FIELD_ITERATOR
        );

        foreach ($batchSelectIterator as $select) {
            foreach ($this->connection->fetchAll($select) as $key => $value) {
                yield $key => $value;
            }
        }
    }

    /**
     * Get the number of unique images of products.
     *
     * @return int
     */
    public function getCountAllProductImages(): int
    {
        $select = $this->getVisibleImagesSelect()
            ->reset('columns')
            ->reset('distinct')
            ->columns(
                new \Zend_Db_Expr('count(distinct value)')
            );

        return (int) $this->connection->fetchOne($select);
    }

    /**
     * Get the number of unique and used images of products.
     *
     * @return int
     */
    public function getCountUsedProductImages(): int
    {
        $select = $this->getUsedImagesSelect()
            ->reset('columns')
            ->reset('distinct')
            ->columns(
                new \Zend_Db_Expr('count(distinct value)')
            );

        return (int) $this->connection->fetchOne($select);
    }

    /**
     * Return select to fetch all products images.
     *
     * @return Select
     */
    private function getVisibleImagesSelect(): Select
    {
        return $this->connection->select()->distinct()
            ->from(
                ['images' => $this->resourceConnection->getTableName(Gallery::GALLERY_TABLE)],
                'value as filepath'
            )->where(
                'disabled = 0'
            );
    }

    /**
     * Return select to fetch all used product images.
     *
     * @return Select
     */
    private function getUsedImagesSelect(): Select
    {
        $productMetadata =  $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $productMetadata->getLinkField();
        $identifierField = $productMetadata->getIdentifierField();

        $select = $this->connection->select()->distinct()
            ->from(
                ['images' => $this->resourceConnection->getTableName(Gallery::GALLERY_TABLE)],
                'value as filepath'
            )->joinInner(
                ['image_value' => $this->resourceConnection->getTableName(Gallery::GALLERY_VALUE_TABLE)],
                'images.value_id = image_value.value_id',
                []
            )->joinInner(
                ['products' => $this->resourceConnection->getTableName('catalog_product_entity')],
                "image_value.$linkField = products.$linkField",
                []
            )->joinInner(
                ['websites' => $this->resourceConnection->getTableName('catalog_product_website')],
                "products.$identifierField = websites.product_id",
                ['GROUP_CONCAT(websites.website_id SEPARATOR \',\') AS website_ids']
            )->where(
                'images.disabled = 0 AND image_value.disabled = 0'
            )->group(
                'websites.product_id'
            );

        return $select;
    }

    /**
     * Get related website IDs for a given image file path.
     *
     * @param string $filepath
     * @return array
     */
    public function getRelatedWebsiteIds(string $filepath): array
    {
        $select = $this->getUsedImagesSelect();
        $select->where('images.value = ?', $filepath);
        $result = array_map(
            fn ($ids) => array_map('intval', explode(',', $ids)),
            $this->connection->fetchPairs($select)
        );

        return $result[$filepath] ?? [];
    }
}
