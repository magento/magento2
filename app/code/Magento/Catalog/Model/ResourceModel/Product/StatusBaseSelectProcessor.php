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
     * @param Config $eavConfig
     */
    public function __construct(
        Config $eavConfig
    ) {
        $this->eavConfig = $eavConfig;
    }

    /**
     * @param Select $select
     * @return Select
     */
    public function process(Select $select)
    {
        $statusAttribute = $this->eavConfig->getAttribute(Product::ENTITY, ProductInterface::STATUS);

        $select->join(
            ['status_attr' => $statusAttribute->getBackendTable()],
            'status_attr.entity_id = ' . self::PRODUCT_RELATION_ALIAS . '.child_id',
            []
        )
            ->where('status_attr.attribute_id = ?', $statusAttribute->getAttributeId())
            ->where('status_attr.value = ?', Status::STATUS_ENABLED);

        return $select;
    }
}
