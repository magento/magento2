<?php
/**
 * Configurable product type resource model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\ResourceModel\Product\Type;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\ConfigurableProduct\Api\Data\OptionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\ConfigurableProduct\Model\AttributeOptionProviderInterface;
use Magento\ConfigurableProduct\Model\ResourceModel\Attribute\OptionProvider;

class Configurable extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Catalog product relation
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Relation
     */
    protected $catalogProductRelation;

    /**
     * @var AttributeOptionProviderInterface
     */
    private $attributeOptionProvider;

    /**
     * @var OptionProvider
     */
    private $optionProvider;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Catalog\Model\ResourceModel\Product\Relation $catalogProductRelation
     * @param string $connectionName
     * @param OptionProvider $optionProvider
     * @param AttributeOptionProviderInterface $attributeOptionProvider
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\Relation $catalogProductRelation,
        $connectionName = null,
        OptionProvider $optionProvider = null,
        AttributeOptionProviderInterface $attributeOptionProvider = null
    ) {
        $this->catalogProductRelation = $catalogProductRelation;
        $this->attributeOptionProvider = $attributeOptionProvider
            ?: ObjectManager::getInstance()->get(AttributeOptionProviderInterface::class);
        $this->optionProvider = $optionProvider ?: ObjectManager::getInstance()->get(OptionProvider::class);

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
        $select = $this->getConnection()->select()->from(
            ['e' => $this->getTable('catalog_product_entity')],
            ['e.entity_id']
        )->where(
            'e.' . $this->optionProvider->getProductEntityLinkField() . '=?',
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

        $productId = $mainProduct->getData($this->optionProvider->getProductEntityLinkField());

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
        $select = $this->getConnection()->select()->from(
            ['l' => $this->getMainTable()],
            ['product_id', 'parent_id']
        )->join(
            ['p' => $this->getTable('catalog_product_entity')],
            'p.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id',
            []
        )->join(
            ['e' => $this->getTable('catalog_product_entity')],
            'e.entity_id = l.product_id AND e.required_options = 0',
            []
        )->where(
            'p.entity_id IN (?)',
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
        $select = $this->getConnection()
            ->select()
            ->from(['l' => $this->getMainTable()], [])
            ->join(
                ['e' => $this->getTable('catalog_product_entity')],
                'e.' . $this->optionProvider->getProductEntityLinkField() . ' = l.parent_id',
                ['e.entity_id']
            )->where('l.product_id IN(?)', $childId);
        $parentIds = $this->getConnection()->fetchCol($select);

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
        $productId = $product->getData($this->optionProvider->getProductEntityLinkField());
        foreach ($attributes as $superAttribute) {
            $attributeId = $superAttribute->getAttributeId();
            $attributesOptionsData[$attributeId] = $this->getAttributeOptions($superAttribute, $productId);
        }
        return $attributesOptionsData;
    }

    /**
     * Load options for attribute
     *
     * @param \Magento\Eav\Model\Entity\Attribute\AbstractAttribute $superAttribute
     * @param int $productId
     * @return array
     */
    public function getAttributeOptions($superAttribute, $productId)
    {
        return $this->attributeOptionProvider->getAttributeOptions($superAttribute, $productId);
    }
}
