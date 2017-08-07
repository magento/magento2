<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product\Website;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Filter products that belongs to current website
 * @since 2.2.0
 */
class SelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var ResourceConnection
     * @since 2.2.0
     */
    private $resource;

    /**
     * @var MetadataPool
     * @since 2.2.0
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     * @since 2.2.0
     */
    private $storeManager;

    /**
     * @param MetadataPool $metadataPool
     * @param ResourceConnection $resource
     * @param StoreManagerInterface $storeManager
     * @since 2.2.0
     */
    public function __construct(
        MetadataPool $metadataPool,
        ResourceConnection $resource,
        StoreManagerInterface $storeManager
    ) {
        $this->metadataPool = $metadataPool;
        $this->resource = $resource;
        $this->storeManager = $storeManager;
    }

    /**
     * Joins website-product relation table to filter products that are only in current website
     *
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function process(Select $select)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $select->joinInner(
            ['pw' => $this->resource->getTableName('catalog_product_website')],
            'pw.product_id = ' . BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS . '.' . $linkField
            . ' AND pw.website_id = ' . $this->storeManager->getWebsite()->getId(),
            []
        );

        return $select;
    }
}
