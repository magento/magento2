<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;

/**
 * Class StatusBaseSelectProcessor
 * @since 2.1.3
 */
class StatusBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var Config
     * @since 2.1.3
     */
    private $eavConfig;

    /**
     * @var MetadataPool
     * @since 2.1.3
     */
    private $metadataPool;

    /**
     * @var StoreResolverInterface
     * @since 2.1.3
     */
    private $storeResolver;

    /**
     * @param Config $eavConfig
     * @param MetadataPool $metadataPool
     * @param StoreResolverInterface $storeResolver
     * @since 2.1.3
     */
    public function __construct(
        Config $eavConfig,
        MetadataPool $metadataPool,
        StoreResolverInterface $storeResolver
    ) {
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->storeResolver = $storeResolver;
    }

    /**
     * @param Select $select
     * @return Select
     * @since 2.1.3
     */
    public function process(Select $select)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $statusAttribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS);

        $select->joinLeft(
            ['status_global_attr' => $statusAttribute->getBackendTable()],
            "status_global_attr.{$linkField} = " . self::PRODUCT_TABLE_ALIAS . ".{$linkField}"
            . ' AND status_global_attr.attribute_id = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_global_attr.store_id = ' . Store::DEFAULT_STORE_ID,
            []
        );

        $select->joinLeft(
            ['status_attr' => $statusAttribute->getBackendTable()],
            "status_attr.{$linkField} = " . self::PRODUCT_TABLE_ALIAS . ".{$linkField}"
            . ' AND status_attr.attribute_id = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_attr.store_id = ' . $this->storeResolver->getCurrentStoreId(),
            []
        );

        $select->where('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED);

        return $select;
    }
}
