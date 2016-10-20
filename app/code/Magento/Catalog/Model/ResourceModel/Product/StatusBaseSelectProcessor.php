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
use Magento\Framework\EntityManager\MetadataPool;

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
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @param Config $eavConfig
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        Config $eavConfig,
        MetadataPool $metadataPool
    ) {
        $this->eavConfig = $eavConfig;
        $this->metadataPool = $metadataPool;
    }

    /**
     * @param Select $select
     * @return Select
     */
    public function process(Select $select)
    {
        $linkField = $this->metadataPool->getMetadata(ProductInterface::class)->getLinkField();
        $statusAttribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS);

        $select->join(
            ['status_attr' => $statusAttribute->getBackendTable()],
            sprintf('status_attr.%s = %s.%1$s', $linkField, self::PRODUCT_TABLE_ALIAS),
            []
        )
            ->where('status_attr.attribute_id = ?', $statusAttribute->getAttributeId())
            ->where('status_attr.value = ?', Status::STATUS_ENABLED);

        return $select;
    }
}
