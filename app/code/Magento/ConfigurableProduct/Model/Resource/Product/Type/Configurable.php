<?php
/**
 * Configurable product type resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Resource\Product\Type;

class Configurable extends \Magento\Framework\Model\Resource\Db\AbstractDb
{
    /**
     * Catalog product relation
     *
     * @var \Magento\Catalog\Model\Resource\Product\Relation
     */
    protected $_catalogProductRelation;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param \Magento\Catalog\Model\Resource\Product\Relation $catalogProductRelation
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        \Magento\Catalog\Model\Resource\Product\Relation $catalogProductRelation
    ) {
        $this->_catalogProductRelation = $catalogProductRelation;
        parent::__construct($resource);
    }

    /**
     * Init resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_super_link', 'link_id');
    }

    /**
     * Save configurable product relations
     *
     * @param \Magento\Catalog\Model\Product $mainProduct the parent id
     * @param array $productIds the children id array
     * @return $this
     */
    public function saveProducts($mainProduct, $productIds)
    {
        $isProductInstance = false;
        if ($mainProduct instanceof \Magento\Catalog\Model\Product) {
            $mainProductId = $mainProduct->getId();
            $isProductInstance = true;
        }
        $old = [];
        if (!$mainProduct->getIsDuplicate()) {
            $old = $mainProduct->getTypeInstance()->getUsedProductIds($mainProduct);
        }

        $insert = array_diff($productIds, $old);
        $delete = array_diff($old, $productIds);

        if ((!empty($insert) || !empty($delete)) && $isProductInstance) {
            $mainProduct->setIsRelationsChanged(true);
        }

        if (!empty($delete)) {
            $where = ['parent_id = ?' => $mainProductId, 'product_id IN(?)' => $delete];
            $this->_getWriteAdapter()->delete($this->getMainTable(), $where);
        }
        if (!empty($insert)) {
            $data = [];
            foreach ($insert as $childId) {
                $data[] = ['product_id' => (int)$childId, 'parent_id' => (int)$mainProductId];
            }
            $this->_getWriteAdapter()->insertMultiple($this->getMainTable(), $data);
        }

        // configurable product relations should be added to relation table
        $this->_catalogProductRelation->processRelations($mainProductId, $productIds);

        return $this;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param bool $required
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenIds($parentId, $required = true)
    {
        $childrenIds = [];
        $select = $this->_getReadAdapter()->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'parent_id']
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.product_id AND e.required_options = 0',
            []
        )->where(
            'parent_id = ?',
            $parentId
        );

        $childrenIds = [0 => []];
        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            $childrenIds[0][$row['product_id']] = $row['product_id'];
        }

        return $childrenIds;
    }

    /**
     * Retrieve parent ids array by requered child
     *
     * @param int|array $childId
     * @return string[]
     */
    public function getParentIdsByChild($childId)
    {
        $parentIds = [];

        $select = $this->_getReadAdapter()->select()->from(
            $this->getMainTable(),
            ['product_id', 'parent_id']
        )->where(
            'product_id IN(?)',
            $childId
        );
        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            $parentIds[] = $row['parent_id'];
        }

        return $parentIds;
    }

    /**
     * Collect product options with values according to the product instance and attributes, that were received
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $attributes
     * @return array
     */
    public function getConfigurableOptions($product, $attributes)
    {
        $attributesOptionsData = [];
        foreach ($attributes as $superAttribute) {
            $select = $this->_getReadAdapter()->select()->from(
                ['super_attribute' => $this->getTable('catalog_product_super_attribute')],
                [
                    'sku' => 'entity.sku',
                    'product_id' => 'super_attribute.product_id',
                    'attribute_code' => 'attribute.attribute_code',
                    'option_title' => 'option_value.value',
                    'pricing_value' => 'attribute_pricing.pricing_value',
                    'pricing_is_percent' => 'attribute_pricing.is_percent'
                ]
            )->joinInner(
                ['product_link' => $this->getTable('catalog_product_super_link')],
                'product_link.parent_id = super_attribute.product_id',
                []
            )->joinInner(
                ['attribute' => $this->getTable('eav_attribute')],
                'attribute.attribute_id = super_attribute.attribute_id',
                []
            )->joinInner(
                ['entity' => $this->getTable('catalog_product_entity')],
                'entity.entity_id = product_link.product_id',
                []
            )->joinInner(
                ['entity_value' => $superAttribute->getBackendTable()],
                implode(
                    ' AND ',
                    [
                        $this->_getReadAdapter()->quoteInto(
                            'entity_value.entity_type_id = ?',
                            $product->getEntityTypeId()
                        ),
                        'entity_value.attribute_id = super_attribute.attribute_id',
                        'entity_value.store_id = 0',
                        'entity_value.entity_id = product_link.product_id'
                    ]
                ),
                []
            )->joinLeft(
                ['option_value' => $this->getTable('eav_attribute_option_value')],
                implode(
                    ' AND ',
                    [
                        'option_value.option_id = entity_value.value',
                        'option_value.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                ),
                []
            )->joinLeft(
                ['attribute_pricing' => $this->getTable('catalog_product_super_attribute_pricing')],
                implode(
                    ' AND ',
                    [
                        'super_attribute.product_super_attribute_id = attribute_pricing.product_super_attribute_id',
                        'entity_value.value = attribute_pricing.value_index'
                    ]
                ),
                []
            )->where(
                'super_attribute.product_id = ?',
                $product->getId()
            );

            $attributesOptionsData[$superAttribute->getAttributeId()] = $this->_getReadAdapter()->fetchAll($select);
        }
        return $attributesOptionsData;
    }
}
