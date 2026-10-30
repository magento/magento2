<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Model\ResourceModel\Product;

use Exception;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Get child product ids from store id and parent product id
 */
class GetStoreSpecificProductChildIds extends AbstractDb
{
    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * Constructor
     *
     *
     * @param MetadataPool $metadataPool
     * @param Context $context
     * @param string $connectionName
     */
    public function __construct(
        MetadataPool $metadataPool,
        Context $context,
        $connectionName = null
    ) {
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    /**
     * Load catalog_product_entity model
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init('catalog_product_entity', 'entity_id');
    }

    /**
     * Process the child product ids based on store id and parent product id
     *
     * @param array $productData
     * @param int $websiteId
     * @return array
     * @throws Exception
     */
    public function process(array $productData, int $websiteId): array
    {
        $connection = $this->getConnection();
        $entityMetadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $linkField = $entityMetadata->getLinkField();

        $select = $connection->select()
            ->from(
                ['cpe' => $this->getTable('catalog_product_entity')],
                []
            )
            ->join(
                ['cpw' => $this->getTable('catalog_product_website')],
                'cpe.entity_id = cpw.product_id',
                []
            )
            ->join(
                ['cpsl' => $this->getTable('catalog_product_super_link')],
                'cpe.entity_id = cpsl.product_id',
                ['product_id']
            )
            ->where('cpsl.parent_id = ?', (int) $productData[$linkField])
            ->where('cpw.website_id = ?', $websiteId);

        return $connection->fetchCol($select);
    }
}
