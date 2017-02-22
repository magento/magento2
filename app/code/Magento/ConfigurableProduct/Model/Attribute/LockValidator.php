<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Attribute;

use Magento\Catalog\Model\Attribute\LockValidatorInterface;
use Magento\Framework\App\ResourceConnection;

class LockValidator implements LockValidatorInterface
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(ResourceConnection $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Check attribute lock state
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param null $attributeSet
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     */
    public function validate(\Magento\Framework\Model\AbstractModel $object, $attributeSet = null)
    {
        $connection = $this->resource->getConnection();
        $attrTable = $this->resource->getTableName('catalog_product_super_attribute');
        $productTable = $this->resource->getTableName('catalog_product_entity');

        $bind = ['attribute_id' => $object->getAttributeId()];
        $select = clone $connection->select();
        $select->reset()->from(
            ['main_table' => $attrTable],
            ['psa_count' => 'COUNT(product_super_attribute_id)']
        )->join(
            ['entity' => $productTable],
            'main_table.product_id = entity.entity_id'
        )->where(
            'main_table.attribute_id = :attribute_id'
        )->group(
            'main_table.attribute_id'
        )->limit(
            1
        );

        if ($attributeSet !== null) {
            $bind['attribute_set_id'] = $attributeSet;
            $select->where('entity.attribute_set_id = :attribute_set_id');
        }

        if ($connection->fetchOne($select, $bind)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This attribute is used in configurable products.')
            );
        }
    }
}
