<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GroupedProduct\Plugin\CatalogSearch\Model\Indexer\Fulltext\Action;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\CatalogSearch\Model\Indexer\Fulltext\Action\GetSearchableProductsSelect as SearchableProductsSelect;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\GroupedProduct\Model\ResourceModel\Product\Link;
use Magento\Store\Model\StoreManagerInterface;

class GetSearchableProductsSelect
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resource,
        StoreManagerInterface $storeManager,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->storeManager = $storeManager;
        $this->metadataPool = $metadataPool;
    }

    /**
     * Exclude grouped products without website-assigned children from the store fulltext index.
     *
     * @param SearchableProductsSelect $subject
     * @param Select $select
     * @param int $storeId
     * @return Select
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
     */
    public function afterExecute(
        SearchableProductsSelect $subject,
        Select $select,
        int $storeId
    ): Select {
        $connection = $this->resource->getConnection();
        $websiteId = (int) $this->storeManager->getStore($storeId)->getWebsiteId();
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $childExistsSelect = $connection->select()
            ->from(['grouped_link' => $this->resource->getTableName('catalog_product_link')], new \Zend_Db_Expr('1'))
            ->join(
                ['grouped_child_website' => $this->resource->getTableName('catalog_product_website')],
                'grouped_child_website.product_id = grouped_link.linked_product_id',
                []
            )
            ->where('grouped_link.product_id = e.' . $linkField)
            ->where('grouped_link.link_type_id = ?', Link::LINK_TYPE_GROUPED)
            ->where('grouped_child_website.website_id = ?', $websiteId)
            ->limit(1);

        $select->where(
            sprintf(
                'e.type_id != %s OR EXISTS (%s)',
                $connection->quote(Grouped::TYPE_CODE),
                $childExistsSelect
            )
        );

        return $select;
    }
}
