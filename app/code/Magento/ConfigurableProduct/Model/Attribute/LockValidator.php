<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Attribute;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Model\Entity\MetadataPool;
use Magento\Catalog\Model\Attribute\LockValidatorInterface;

/**
 * Class LockValidator
 */
class LockValidator implements LockValidatorInterface
{
    /**
     * @var Resource
     */
    protected $resource;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * Constructor
     *
     * @param ResourceConnection $resource
     * @param MetadataPool $metadataPool
     */
    public function __construct(
        ResourceConnection $resource,
        MetadataPool $metadataPool
    ) {
        $this->resource = $resource;
        $this->metadataPool = $metadataPool;
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
        $metadata = $this->metadataPool->getMetadata(ProductInterface::class);
        $connection = $this->resource->getConnection();

        $bind = ['attribute_id' => $object->getAttributeId()];

        $select = clone $connection->select();
        $select->reset()
            ->from(
                ['main_table' => $this->resource->getTableName('catalog_product_super_attribute')],
                ['psa_count' => 'COUNT(product_super_attribute_id)']
            )->join(
                ['entity' => $this->resource->getTableName('catalog_product_entity')],
                'main_table.product_id = entity.' . $metadata->getLinkField()
            )->where('main_table.attribute_id = :attribute_id')
            ->group('main_table.attribute_id')
            ->limit(1);

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
