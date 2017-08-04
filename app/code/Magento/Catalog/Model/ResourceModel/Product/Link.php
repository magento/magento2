<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\ResourceModel\Product;

/**
 * Catalog product link resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Link extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Product Link Attributes Table
     *
     * @var string
     */
    protected $_attributesTable;

    /**
     * Catalog product relation
     *
     * @var Relation
     */
    protected $_catalogProductRelation;

    /**
     * Link constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param Relation $catalogProductRelation
     * @param string|null $connectionName
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        Relation $catalogProductRelation,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
    }

    /**
     * Define main table name and attributes table
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_product_link', 'link_id');
        $this->_attributesTable = $this->getTable('catalog_product_link_attribute');
    }

    /**
     * Delete product link by link_id
     *
     * @param int $linkId
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    public function deleteProductLink($linkId)
    {
        return $this->getConnection()->delete($this->getMainTable(), ['link_id = ?' => $linkId]);
    }

    /**
     * Retrieve product link_id by link product id and type id
     *
     * @param int $parentId
     * @param int $linkedProductId
     * @param int $typeId
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.1.0
     */
    public function getProductLinkId($parentId, $linkedProductId, $typeId)
    {
        $connection = $this->getConnection();

        $bind = [
            ':product_id' => (int)$parentId,
            ':link_type_id' => (int)$typeId,
            ':linked_product_id' => (int)$linkedProductId
        ];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['link_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        )->where(
            'linked_product_id = :linked_product_id'
        );

        return $connection->fetchOne($select, $bind);
    }

    /**
     * Check if product has links.
     *
     * @param int $parentId ID of product
     * @return bool
     * @since 2.1.3
     */
    public function hasProductLinks($parentId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['count' => new \Zend_Db_Expr('COUNT(*)')]
        )->where(
            'product_id = :product_id'
        ) ;

        return $connection->fetchOne(
            $select,
            [
                'product_id' => $parentId
            ]
        ) > 0;
    }

    /**
     * Save Product Links process
     *
     * @param int $parentId
     * @param array $data
     * @param int $typeId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function saveProductLinks($parentId, $data, $typeId)
    {
        if (!is_array($data)) {
            $data = [];
        }

        $attributes = $this->getAttributesByType($typeId);
        $connection = $this->getConnection();

        $bind = [':product_id' => (int)$parentId, ':link_type_id' => (int)$typeId];
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['linked_product_id', 'link_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $links = $connection->fetchPairs($select, $bind);

        foreach ($data as $linkedProductId => $linkInfo) {
            $linkId = null;
            if (isset($links[$linkedProductId])) {
                $linkId = $links[$linkedProductId];
                unset($links[$linkedProductId]);
            } else {
                $bind = [
                    'product_id' => $parentId,
                    'linked_product_id' => $linkedProductId,
                    'link_type_id' => $typeId,
                ];
                $connection->insert($this->getMainTable(), $bind);
                $linkId = $connection->lastInsertId($this->getMainTable());
            }

            foreach ($attributes as $attributeInfo) {
                $attributeTable = $this->getAttributeTypeTable($attributeInfo['type']);
                if ($attributeTable) {
                    if (isset($linkInfo[$attributeInfo['code']])) {
                        $value = $this->_prepareAttributeValue(
                            $attributeInfo['type'],
                            $linkInfo[$attributeInfo['code']]
                        );
                        $bind = [
                            'product_link_attribute_id' => $attributeInfo['id'],
                            'link_id' => $linkId,
                            'value' => $value,
                        ];
                        $connection->insertOnDuplicate($attributeTable, $bind, ['value']);
                    } else {
                        $connection->delete(
                            $attributeTable,
                            ['link_id = ?' => $linkId, 'product_link_attribute_id = ?' => $attributeInfo['id']]
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Prepare link attribute value by attribute type
     *
     * @param string $type
     * @param mixed $value
     * @return mixed
     */
    protected function _prepareAttributeValue($type, $value)
    {
        if ($type == 'int') {
            $value = (int)$value;
        } elseif ($type == 'decimal') {
            $value = (double)sprintf('%F', $value);
        }
        return $value;
    }

    /**
     * Retrieve product link attributes by link type
     *
     * @param int $typeId
     * @return array
     */
    public function getAttributesByType($typeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->_attributesTable,
            ['id' => 'product_link_attribute_id', 'code' => 'product_link_attribute_code', 'type' => 'data_type']
        )->where(
            'link_type_id = ?',
            $typeId
        );
        return $connection->fetchAll($select);
    }

    /**
     * Returns table for link attribute by attribute type
     *
     * @param string $type
     * @return string
     */
    public function getAttributeTypeTable($type)
    {
        return $this->getTable('catalog_product_link_attribute_' . $type);
    }

    /**
     * Retrieve Required children ids
     * Return grouped array, ex array(
     *   group => array(ids)
     * )
     *
     * @param int $parentId
     * @param int $typeId
     * @return array
     */
    public function getChildrenIds($parentId, $typeId)
    {
        $connection = $this->getConnection();
        $childrenIds = [];
        $bind = [':product_id' => (int)$parentId, ':link_type_id' => (int)$typeId];
        $select = $connection->select()->from(
            ['l' => $this->getMainTable()],
            ['linked_product_id']
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $childrenIds[$typeId] = [];
        $result = $connection->fetchAll($select, $bind);
        foreach ($result as $row) {
            $childrenIds[$typeId][$row['linked_product_id']] = $row['linked_product_id'];
        }

        return $childrenIds;
    }

    /**
     * Retrieve parent ids array by required child
     *
     * @param int|array $childId
     * @param int $typeId
     * @return string[]
     */
    public function getParentIdsByChild($childId, $typeId)
    {
        $parentIds = [];
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable(),
            ['product_id', 'linked_product_id']
        )->where(
            'linked_product_id IN(?)',
            $childId
        )->where(
            'link_type_id = ?',
            $typeId
        );

        $result = $connection->fetchAll($select);
        foreach ($result as $row) {
            $parentIds[] = $row['product_id'];
        }

        return $parentIds;
    }
}
