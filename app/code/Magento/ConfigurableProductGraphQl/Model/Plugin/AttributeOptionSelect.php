<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProductGraphQl\Model\Plugin;

use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionSelectBuilder;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable\Attribute;
use Magento\Eav\Model\Entity\Attribute\AbstractAttribute;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\DB\Select;

/**
 * This plugin makes limit select with disabled child products
 */
class AttributeOptionSelect
{
    /**
     * Configurable Attribute Resource Model.
     *
     * @var Attribute
     */
    private $attributeResource;

    /**
     * @param Attribute $attributeResource
     */
    public function __construct(Attribute $attributeResource)
    {
        $this->attributeResource = $attributeResource;
    }

    /**
     * @param OptionSelectBuilder $subject
     * @param Select $select
     * @param AbstractAttribute $superAttribute
     * @return Select
     */
    public function afterGetSelect(
        OptionSelectBuilder $subject,
        Select $select,
        AbstractAttribute $superAttribute
    ) {
        $select->joinInner(
            ['entity_attr_value' => $superAttribute->getBackendTable()],
            implode(
                ' AND ',
                [
                    'entity_value.store_id = 0',
                    'entity_attr_value.entity_id = entity.entity_id',
                    'entity_attr_value.value = ' . Status::STATUS_ENABLED
                ]
            ),
            []
        )->joinInner(
            ['attribute_status' => $this->attributeResource->getTable('eav_attribute')],
            implode(
                ' AND ',
                [
                    "attribute_status.attribute_code = '" . ProductInterface::STATUS . "'",
                    'entity_attr_value.attribute_id = attribute_status.attribute_id'
                ]
            ),
            []
        );

        return $select;
    }
}
