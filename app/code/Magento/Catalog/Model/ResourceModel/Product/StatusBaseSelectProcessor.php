<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Eav\Model\Config;
use Magento\Framework\DB\Select;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\Store;

/**
 * Class StatusBaseSelectProcessor
 */
class StatusBaseSelectProcessor implements BaseSelectProcessorInterface
{
    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var StoreResolverInterface
     */
    private $storeResolver;

    /**
     * @param Config $eavConfig
     * @param StoreResolverInterface $storeResolver
     */
    public function __construct(
        Config $eavConfig,
        StoreResolverInterface $storeResolver
    ) {
        $this->eavConfig = $eavConfig;
        $this->storeResolver = $storeResolver;
    }

    /**
     * @param Select $select
     * @return Select
     */
    public function process(Select $select)
    {
        $statusAttribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS);

        $select->joinLeft(
            ['status_global_attr' => $statusAttribute->getBackendTable()],
            "status_global_attr.entity_id = " . self::PRODUCT_RELATION_ALIAS . ".child_id"
            . ' AND status_global_attr.attribute_id = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_global_attr.store_id = ' . Store::DEFAULT_STORE_ID,
            []
        );

        $select->joinLeft(
            ['status_attr' => $statusAttribute->getBackendTable()],
            "status_attr.entity_id = " . self::PRODUCT_RELATION_ALIAS . ".child_id"
            . ' AND status_attr.attribute_id = ' . (int)$statusAttribute->getAttributeId()
            . ' AND status_attr.store_id = ' . $this->storeResolver->getCurrentStoreId(),
            []
        );

        $select->where('IFNULL(status_attr.value, status_global_attr.value) = ?', Status::STATUS_ENABLED);

        return $select;
    }
}
