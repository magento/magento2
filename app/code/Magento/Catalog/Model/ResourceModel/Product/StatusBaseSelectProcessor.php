<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
use Magento\Store\Model\StoreManagerInterface;

class StatusBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Config $eavConfig
     * @param MetadataPool $metadataPool
     * @param StoreResolverInterface $storeResolver
     * @param StoreManagerInterface $storeManager
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        Config $eavConfig,
        MetadataPool $metadataPool,
        StoreResolverInterface $storeResolver,
        ?StoreManagerInterface $storeManager = null
    ) {
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
        $this->storeManager = $storeManager ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
    }

    /**
     * @inheritdoc
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
            . ' AND status_attr.store_id = ' . $this->storeManager->getStore()->getId(),
            []
        );

        $select->where('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED);

        return $select;
    }
}
