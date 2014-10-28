<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Model\Resource\Product;

/**
 * Catalog product link resource model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Link extends \Magento\Framework\Model\Resource\Db\AbstractDb
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
     * @param \Magento\Framework\App\Resource $resource
     * @param Relation $catalogProductRelation
     */
    public function __construct(\Magento\Framework\App\Resource $resource, Relation $catalogProductRelation)
    {
        $this->_catalogProductRelation = $catalogProductRelation;
        parent::__construct($resource);
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
     * Save Product Links process
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $data
     * @param int $typeId
     * @return $this
     */
    public function saveProductLinks($product, $data, $typeId)
    {
        if (!is_array($data)) {
            $data = array();
        }

        $attributes = $this->getAttributesByType($typeId);
        $adapter = $this->_getWriteAdapter();

        $bind = array(':product_id' => (int)$product->getId(), ':link_type_id' => (int)$typeId);
        $select = $adapter->select()->from(
            $this->getMainTable(),
            array('linked_product_id', 'link_id')
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $links = $adapter->fetchPairs($select, $bind);

        $deleteIds = array();
        foreach ($links as $linkedProductId => $linkId) {
            if (!isset($data[$linkedProductId])) {
                $deleteIds[] = (int)$linkId;
            }
        }
        if (!empty($deleteIds)) {
            $adapter->delete($this->getMainTable(), array('link_id IN (?)' => $deleteIds));
        }

        foreach ($data as $linkedProductId => $linkInfo) {
            $linkId = null;
            if (isset($links[$linkedProductId])) {
                $linkId = $links[$linkedProductId];
                unset($links[$linkedProductId]);
            } else {
                $bind = array(
                    'product_id' => $product->getId(),
                    'linked_product_id' => $linkedProductId,
                    'link_type_id' => $typeId
                );
                $adapter->insert($this->getMainTable(), $bind);
                $linkId = $adapter->lastInsertId($this->getMainTable());
            }

            foreach ($attributes as $attributeInfo) {
                $attributeTable = $this->getAttributeTypeTable($attributeInfo['type']);
                if ($attributeTable) {
                    if (isset($linkInfo[$attributeInfo['code']])) {
                        $value = $this->_prepareAttributeValue(
                            $attributeInfo['type'],
                            $linkInfo[$attributeInfo['code']]
                        );
                        $bind = array(
                            'product_link_attribute_id' => $attributeInfo['id'],
                            'link_id' => $linkId,
                            'value' => $value
                        );
                        $adapter->insertOnDuplicate($attributeTable, $bind, array('value'));
                    } else {
                        $adapter->delete(
                            $attributeTable,
                            array('link_id = ?' => $linkId, 'product_link_attribute_id = ?' => $attributeInfo['id'])
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
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->_attributesTable,
            array('id' => 'product_link_attribute_id', 'code' => 'product_link_attribute_code', 'type' => 'data_type')
        )->where(
            'link_type_id = ?',
            $typeId
        );
        return $adapter->fetchAll($select);
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
        $adapter = $this->_getReadAdapter();
        $childrenIds = array();
        $bind = array(':product_id' => (int)$parentId, ':link_type_id' => (int)$typeId);
        $select = $adapter->select()->from(
            array('l' => $this->getMainTable()),
            array('linked_product_id')
        )->where(
            'product_id = :product_id'
        )->where(
            'link_type_id = :link_type_id'
        );

        $childrenIds[$typeId] = array();
        $result = $adapter->fetchAll($select, $bind);
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
        $parentIds = array();
        $adapter = $this->_getReadAdapter();
        $select = $adapter->select()->from(
            $this->getMainTable(),
            array('product_id', 'linked_product_id')
        )->where(
            'linked_product_id IN(?)',
            $childId
        )->where(
            'link_type_id = ?',
            $typeId
        );

        $result = $adapter->fetchAll($select);
        foreach ($result as $row) {
            $parentIds[] = $row['product_id'];
        }

        return $parentIds;
    }
}
