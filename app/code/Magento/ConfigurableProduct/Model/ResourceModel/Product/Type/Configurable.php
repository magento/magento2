<?php
/**
 * Configurable product type resource model
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;

class Configurable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Catalog product relation
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation
     */
    protected $catalogProductRelation;

    /**
     * @var \Magento\Framework\Model\Entity\MetadataPool
     */
    private $metadataPool;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Relation $catalogProductRelation
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Relation $catalogProductRelation,
        $connectionName = null
    ) {
        $this->catalogProductRelation = $catalogProductRelation;
        parent::__construct($context, $connectionName);
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
     * Get product entity id by product attribute
     *
     * @param OptionInterface $option
     * @return int
     */
    public function getEntityIdByAttribute(OptionInterface $option)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);

        $select = $this->getConnection()->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['e.entity_id']
        )->where(
            'e.' . $metadata->getLinkField() . '=?',
            $option->getProductId()
        )->limit(1);

        return (int) $this->getConnection()->fetchOne($select);
    }

    /**
     * Save configurable product relations
     *
     * @param \Magento\Catalog\Model\Product $mainProduct the parent id
     * @param array $productIds the children id array
     * @return $this
     */
    public function saveProducts($mainProduct, array $productIds)
    {
        if (!$mainProduct instanceof ProductInterface) {
            return $this;
        }

        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $productId = $mainProduct->getData($metadata->getLinkField());

        $data = [];
        foreach ($productIds as $id) {
            $data[] = ['product_id' => (int) $id, 'parent_id' => (int) $productId];
        }

        if (!empty($data)) {
            $this->getConnection()->insertOnDuplicate(
                $this->getMainTable(),
                $data,
                ['product_id', 'parent_id']
            );
        }

        $where = ['parent_id = ?' => $productId];
        if (!empty($productIds)) {
            $where['product_id NOT IN(?)'] = $productIds;
        }

        $this->getConnection()->delete($this->getMainTable(), $where);

        // configurable product relations should be added to relation table
        $this->catalogProductRelation->processRelations($productId, $productIds);

        return $this;
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int|array $parentId
     * @param bool $required
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getChildrenIds($parentId, $required = true)
    {
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $select = $this->getConnection()->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['l.product_id']
        )->join(
            ['l' => $this->getMainTable()],
            'l.parent_id = e.' . $metadata->getLinkField(),
            []
        )->where(
            'e.entity_id IN (?) AND e.required_options = 0',
            $parentId
        );

        $childrenIds = [0 => []];
        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $childrenIds[0][$row['product_id']] = $row['product_id'];
        }

        return $childrenIds;
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @return string[]
     */
    public function getParentIdsByChild($childId)
    {
        $parentIds = [];

        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);

        $select = $this->getConnection()
            ->select()
            ->from(['l' => $this->getMainTable()], [])
            ->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.' . $metadata->getLinkField() . ' = l.parent_id',
                ['e.entity_id']
            )->where('l.product_id IN(?)', $childId);

        foreach ($this->getConnection()->fetchAll($select) as $row) {
            $parentIds[] = $row['entity_id'];
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
        $metadata = $this->getMetadataPool()->getMetadata(ProductInterface::class);
        $productId = $product->getData($metadata->getLinkField());
        foreach ($attributes as $superAttribute) {
            $select = $this->getConnection()->select()->from(
                ['super_attribute' => $this->getTable('catalog_product_super_attribute')],
                [
                    'sku' => 'entity.sku',
                    'product_id' => 'product_entity.entity_id',
                    'attribute_code' => 'attribute.attribute_code',
                    'option_title' => 'option_value.value',
                    'super_attribute_label' => 'attribute_label.value',
                ]
            )->joinInner(
                ['product_entity' => $this->getTable('catalog_product_entity')],
                "product_entity.{$metadata->getLinkField()} = super_attribute.product_id",
                []
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
                        'entity_value.attribute_id = super_attribute.attribute_id',
                        'entity_value.store_id = 0',
                        "entity_value.{$metadata->getLinkField()} = product_link.product_id"
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
                ['attribute_label' => $this->getTable('catalog_product_super_attribute_label')],
                implode(
                    ' AND ',
                    [
                        'super_attribute.product_super_attribute_id = attribute_label.product_super_attribute_id',
                        'attribute_label.store_id = ' . \Magento\Store\Model\Store::DEFAULT_STORE_ID
                    ]
                ),
                []
            )->where(
                'super_attribute.product_id = ?',
                $productId
            );
            $attributesOptionsData[$superAttribute->getAttributeId()] = $this->getConnection()->fetchAll($select);
        }
        return $attributesOptionsData;
    }

    /**
     * @return \Magento\Framework\Model\Entity\MetadataPool
     */
    protected function getMetadataPool()
    {
        if (!isset($this->metadataPool)) {
            $this->metadataPool = \Magento\Framework\App\ObjectManager::getInstance()
                ->get('Magento\Framework\Model\Entity\MetadataPool');
        }
        return $this->metadataPool;
    }

    /**
     * @param \Magento\Framework\Model\Entity\MetadataPool $metadataPool
     */
    public function setMetadataPool(\Magento\Framework\Model\Entity\MetadataPool $metadataPool)
    {
        $this->metadataPool = $metadataPool;
    }
}
